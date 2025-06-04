<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class FavoriteController extends Controller
{
    public function store(string $pratoId): JsonResponse
    {
        $user = Auth::user();

        try {
            Favorite::create([
                'user_id' => $user->id,
                'prato_id' => $pratoId
            ]);

            return response()->json([
                'message' => 'Prato adicionado aos favoritos',
                'is_favorite' => true
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao adicionar prato aos favoritos',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    public function getUserFavorites(): JsonResponse
    {
        $user = Auth::user();

        try {
            $favorites = Favorite::select('id', 'user_id', 'prato_id')
                ->where('user_id', $user->id)
                ->with(['prato:id,nome,descricao,imagem'])
                ->get();

            return response()->json(['favorites' => $favorites]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMostFavoritedDishes(): JsonResponse
    {
        try {
            $mostFavorited = Favorite::select('prato_id', DB::raw('count(*) as total_favorites'))
                ->with(['prato:id,nome,descricao,imagem,categoria']) // Carrega os dados do prato
                ->groupBy('prato_id')
                ->orderBy('total_favorites', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'prato' => $item->prato,
                        'total_favoritos' => $item->total_favorites
                    ];
                });

            return response()->json(['most_favorited' => $mostFavorited]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar pratos mais favoritados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $pratoId): JsonResponse
    {
        try {
            $user = Auth::user();

            Favorite::where('user_id', $user->id)->where('prato_id', $pratoId)->delete();

            return response()->json(['message' => 'Prato removido dos favoritos']);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erro ao remover prato dos favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
