<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ReservasHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReservaRequest;
use App\Mail\CancelReservation;
use App\Mail\ConfirmReservation;
use App\Models\Reserva;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
    public function index(Request $request): JsonResponse
    {
        $query = $this->reserva->with('user')->orderBy('id', 'DESC');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        ReservasHelper::applySearchFilter($request, $query);

        $reservas = $query->paginate(5, ['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'name', 'email', 'phone', 'status']);

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

            if ($user && !ReservasHelper::checkReservationLimit($user->id)) {
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
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'confirmacao_token' => Str::random(32),
                'status' => 'pendente'
            ]);

            Mail::to($user->email)->send(new ConfirmReservation([
                'name' => $user->name,
                'data' => $reserva->data,
                'hora' => $reserva->hora,
                'quantidade_pessoas' => $reserva->quantidade_cadeiras,
                'link' => url("/api/confirmar-reserva/{$reserva->confirmacao_token}")
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Reserva feita com sucesso!',
                'reserva' => $reserva->only(['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'name', 'email', 'phone', 'status'])
            ], 201);

        } catch(Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ocorreu um erro ao realizar reserva',
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

        if (!$reserva) {
            return response()->json([
                'message' => 'Reserva não encontrada'
            ], 404);
        }

        return response()->json($reserva->only(['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'user', 'name', 'email', 'phone', 'status']), 200);
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

            if ($reserva->status === 'confirmada') {
                return response()->json([
                    'message' => 'Não é possível editar uma reserva já confirmada'
                ], 403);
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

            if (Auth::user()->role === 'admin') {
                $reserva->update([
                    'data' => $request->data,
                    'hora' => $request->hora,
                    'quantidade_cadeiras' => $request->quantidade_cadeiras,
                ]);

                DB::commit();

                Mail::to($reserva->email)->send(new ConfirmReservation([
                    'name' => $reserva->name,
                    'data' => $reserva->data,
                    'hora' => $reserva->hora,
                    'quantidade_pessoas' => $reserva->quantidade_cadeiras,
                    'titulo' => 'Reserva alterada pelo estabelecimento',
                    'link' => url("/api/confirmar-reserva/{$reserva->confirmacao_token}")
                ]));

                return response()->json([
                    'message' => 'Reserva alterada pelo administrador!'
                ], 200);
            }

            $reserva->update([
                'data' => $request->data,
                'hora' => $request->hora,
                'quantidade_cadeiras' => $request->quantidade_cadeiras
            ]);

            Mail::to($user->email)->send(new ConfirmReservation([
                'name' => $user->name,
                'data' => $reserva->data,
                'hora' => $reserva->hora,
                'quantidade_pessoas' => $reserva->quantidade_cadeiras,
                'link' => url("/api/confirmar-reserva/{$reserva->confirmacao_token}")
            ]));

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

    public function notLoggedUserStore(ReservaRequest $request): JsonResponse
    {
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

            if ($request->quantidade_cadeiras > 12) {
                return response()->json([
                    'message' => 'Reservas acima de 12 pessoas devem ser feitas diretamente com o restaurante.'
                ], 400);
            }

            $reserva = $this->reserva->create([
                'user_id' => null,
                'data' => $request->data,
                'hora' => $request->hora,
                'quantidade_cadeiras' => $request->quantidade_cadeiras,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'confirmacao_token' => Str::random(32),
                'status' => 'pendente'
            ]);

            Mail::to($request->email)->send(new ConfirmReservation([
                'name' => $request->name,
                'data' => $reserva->data,
                'hora' => $reserva->hora,
                'quantidade_pessoas' => $reserva->quantidade_cadeiras,
                'link' => url("/api/confirmar-reserva/{$reserva->confirmacao_token}")
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Reserva feita com sucesso!',
                'reserva' => $reserva->only(['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'status'])
            ], 201);

        } catch(Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocorreu um erro ao realizar reserva',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function cancel(Request $request, string $id)
    {
        try {
            DB::beginTransaction();

            $reserva = $this->reserva->find($id);

            if (!$reserva) {
                return response()->json([
                    'message' => 'Reserva não encontrada'
                ], 404);
            }

            $reserva->status = 'cancelada';
            $reserva->user_id = null;
            $reserva->canceled_at = now();
            $reserva->save();

            if (Auth::user()->role === 'admin') {
                $reserva->motivo_cancelamento = $request->motivo_cancelamento;

                $reserva->save();
                DB::commit();

                Mail::to($reserva->email)->send(new CancelReservation([
                    'name' => $reserva->name,
                    'data' => $reserva->data,
                    'hora' => $reserva->hora,
                    'quantidade_pessoas' => $reserva->quantidade_cadeiras,
                    'motivo_cancelamento' => $reserva->motivo_cancelamento,
                    'titulo_cancelamento' => 'Reserva cancelada pelo estabelecimento'
                ]));

                return response()->json([
                    'message' => 'Reserva cancelada pelo administrador!'
                ], 200);
            }

            Mail::to($reserva->email)->send(new CancelReservation([
                'name' => $reserva->name,
                'data' => $reserva->data,
                'hora' => $reserva->hora,
                'quantidade_pessoas' => $reserva->quantidade_cadeiras,
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Reserva cancelada com sucesso',
                'reserva' => $reserva->only(['id', 'user_id', 'data', 'hora', 'quantidade_cadeiras', 'status'])
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Ocorreu um erro ao fazer o cancelamento da reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
