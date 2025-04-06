<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TokenRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TokenController extends Controller
{
    public function store(TokenRequest $request)
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if(!$user) {
            return response()->json(['error' => 'E-mail ou senha inválido(a)'], 401);
        }

        if(!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'E-mail ou senha inválido(a)'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso!',
            'token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
        ], 200);
    }

    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso',
        ]);
    }
}
