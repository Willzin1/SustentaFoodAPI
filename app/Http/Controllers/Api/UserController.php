<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public readonly User $user;
    public function __construct()
    {
        $this->user = new User;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = $this->user->orderBy('id', 'DESC')->get(['id', 'name', 'email', 'phone', 'role']);
        return response()->json($users, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try{
            $user = $this->user->create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'role' => 'user',
            ]);

            $user->sendEmailVerificationNotification();

            DB::commit();

            return response()->json([
                'message' => 'Usuário cadastrado com sucesso',
                'user' => $user->only(['id', 'name', 'email', 'phone', 'role']),
            ], 201);

        }catch(Exception $e){
            DB::rollBack();

            $errors = [
                'message' => 'Usuário não cadastrado',
                'errors' => [
                    'exception' => $e->getMessage(),
                ]
            ];

            return response()->json($errors, 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $id): JsonResponse
    {
        $user = $this->user->find($id);

        if(!$user) {
            return response()->json([
                'message' => 'Usuário não encontrado'
            ], 404);
        }

        return response()->json($user->only(['id', 'name', 'email', 'phone', 'role']), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, String $id): JsonResponse
    {
        $user = $this->user->find($id);
        if(!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        try {
            DB::beginTransaction();

            $request->validate([
                'name' => 'required',
                'phone' => 'required|phone:br'
            ], [
                'name.required' => 'Nome é obrigatório',
                'phone.required' => 'Telefone é obrigatório',
                'phone.phone' => 'Insira um telefone válido'
            ]);

            $user->update([
                'name' => $request->name,
                'phone' => $request->phone
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Informações alteradas com sucesso',
                'user' => $user->only(['id', 'name', 'email', 'phone']),
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();

            $errors = collect($e->validator->errors()->all());

            return response()->json([
                'message' => 'Erro ao alterar informações',
                'error' => $errors
            ], 422);

        } catch (Exception $e){
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao alterar informações',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $id): JsonResponse
    {
        $user = User::find($id);
        if(!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        try {
            $user->delete();

            return response()->json(['message' => 'Usuário deletado com sucesso'], 200);

        } catch(Exception $e) {
            return response()->json([
                'message' => 'Erro ao deletar usuário',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
