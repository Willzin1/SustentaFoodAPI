<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ReservasHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservaRequest;
use App\Models\Reserva;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{

    public readonly Reserva $reserva;
    public function __construct()
    {
        $this->reserva = new Reserva;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $query = $this->reserva->with('user')->orderBy('id', 'DESC');

        if (request()->has('user_id')) {
            $query->where('user_id', request('user_id'));

            $reservas = $query->paginate(4, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras']);
            return response()->json($reservas, 200);
        }

        $reservas = $query->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras']);
        return response()->json($reservas, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReservaRequest $request): JsonResponse
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $isAvailable = ReservasHelper::checkAvailability(
                $request->data,
                $request->hora,
                $request->quantidade_cadeiras
            );

            if (! $isAvailable) {
                return response()->json([
                    'message' => 'Reserva indisponível para esse horário.'
                ], 400);
            }

            $reservationLimit = ReservasHelper::checkReservationLimit(
                $user->id
            );

            if (! $reservationLimit) {
                return response()->json([
                    'message' => 'Somente 4 reservas por usuário'
                ], 400);
            }

            if ($request->quantidade_cadeiras > 12) {
                return response()->json([
                    'message' => 'Reservas acima de 12 pessoas devem ser feitas diretamente com o restaurante.'
                ], 400);
            }

            $reserva = $this->reserva->create([
                'user_id' => $user->id,
                'data' => $request->data,
                'hora' => $request->hora,
                'quantidade_cadeiras' => $request->quantidade_cadeiras,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Reserva feita com sucesso!',
                'reserva' => $reserva->only(['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras'])
            ], 201);

        } catch(Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ocorreu um erro ao fazer reserva',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $reserva = $this->reserva->find($id);
        $reserva->load('user');

        if(!$reserva) {
            return response()->json([
                'message' => 'Reserva não encontrada'
            ], 404);
        }

        return response()->json($reserva->only(['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'user']), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReservaRequest $request, string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $reserva = $this->reserva->find($id);

            if (!$reserva) {
                return response()->json([
                    'message' => 'Reserva não encontrada'
                ], 404);
            }

            $isAvailable = ReservasHelper::checkAvailability(
                $request->data,
                $request->hora,
                $request->quantidade_cadeiras
            );

            if (! $isAvailable) {
                return response()->json([
                    'message' => 'Reserva indisponível para esse horário.'
                ], 400);
            }

            if ($request->quantidade_cadeiras > 12) {
                return response()->json([
                    'message' => 'Reservas acima de 12 pessoas devem ser feitas diretamente com o restaurante.'
                ], 400);
            }

            $reserva->update([
                'data' => $request->data,
                'hora' => $request->hora,
                'quantidade_cadeiras' => $request->quantidade_cadeiras
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Informações alteradas com sucesso',
                'reserva' => $reserva->only(['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras']),
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            $errors = [
                'message' => 'Ocorreu um erro ao alterar reserva',
                'error' => $e->getMessage()
            ];

            return response()->json($errors, 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $reserva = $this->reserva->find($id);

            if (!$reserva) {
                return response()->json([
                    'message' => 'Reserva não encontrada'
                ], 404);
            }

            $reserva->delete();

            DB::commit();

            return response()->json([
                'message' => 'Reserva excluída com sucesso',
                'user' => $reserva->only(['user_id']),
            ], 200);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao excluir reserva',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
