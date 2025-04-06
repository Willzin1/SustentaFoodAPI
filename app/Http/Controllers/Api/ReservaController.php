<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservaRequest;
use App\Models\Reserva;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $reservas = $this->reserva->with('user')->orderBy('id', 'DESC')->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras']);

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

            $reserva = $this->reserva->create([
                'user_id' => $user->id,
                'data' => $request->data,
                'hora' => $request->hora,
                'quantidade_cadeiras' => $request->quantidade_cadeiras,
            ]);

            if (!$reserva) {
                return response()->json([
                    'message' => 'Não foi possível realizar reserva'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'message' => 'Reserva feita com sucesso!',
                'reserva' => $reserva
            ], 201);

        } catch(Exception $e) {
            DB::rollBack();

            $errors = [
                'message' => 'Ocorreu um erro ao fazer reserva',
                'error' => $e->getMessage()
            ];

            return response()->json($errors, 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $reserva = $this->reserva->find($id);

        if(!$reserva) {
            return response()->json([
                'message' => 'Reserva não encontrada'
            ], 404);
        }

        return response()->json($reserva->only('id', 'user_id', 'data', 'hora', 'quantidade_cadeiras'), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReservaRequest $request, string $id): JsonResponse
    {
        $user = Auth::user();

        try {
            DB::beginTransaction();

            $reserva = $this->reserva->find($id);

            if (!$reserva) {
                return response()->json([
                    'message' => 'Reserva não encontrada'
                ], 404);
            }

            $reserva->update([
                'data' => $request->data,
                'hora' => $request->hora,
                'quantidade_cadeiras' => $request->quantidade_cadeiras
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Informações alteradas com sucesso',
                'reserva' => $reserva->only(['data', 'hora', 'quantidade_cadeiras']),
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
                'message' => 'Reserva excluída com sucesso'
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
