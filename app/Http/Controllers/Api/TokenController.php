<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TokenRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Controller responsável pelo gerenciamento de tokens de autenticação
 */
class TokenController extends Controller
{
    /**
     * Cria um novo token de acesso
     *
     * @param TokenRequest $request Request com credenciais de login
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - message: Mensagem de sucesso
     * - token: Token de acesso gerado
     * - token_type: Tipo do token (bearer)
     * - user: Dados do usuário autenticado
     *
     * Validações:
     * - Verifica se email existe
     * - Valida senha
     * - Verifica se email foi confirmado
     *
     * Códigos de resposta:
     * - 200: Login realizado com sucesso
     * - 401: Credenciais inválidas
     * - 403: Email não confirmado
     */
    public function store(TokenRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'E-mail ou senha inválido(a)'], 401);
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'E-mail ou senha inválido(a)'], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Por favor, faça a confirmação do e-mail'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso!',
            'token' => $token,
            'token_type' => 'bearer',
            'user' => $user->except(['password', 'email_verified_at', 'created_at', 'updated_at', 'remember_token']),
        ], 200);
    }

    /**
     * Remove o token de acesso atual (logout)
     *
     * @param Request $request Request com token atual
     * @return JsonResponse Retorna uma resposta JSON contendo:
     * - message: Mensagem de confirmação do logout
     *
     * Requer:
     * - Usuário autenticado
     * - Token válido
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso',
        ]);
    }
}
