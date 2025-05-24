<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function toggleFavorite(Request $request, $pratoId)
    {
        $userId = auth()->id();
        
        $favorite = Favorite::where('user_id', $userId)
            ->where('prato_id', $pratoId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['message' => 'Prato removido dos favoritos', 'is_favorite' => false]);
        }

        Favorite::create([
            'user_id' => $userId,
            'prato_id' => $pratoId
        ]);

        return response()->json(['message' => 'Prato adicionado aos favoritos', 'is_favorite' => true]);
    }

    public function getUserFavorites()
    {
        $favorites = Favorite::select('id', 'user_id', 'prato_id')
            ->where('user_id', auth()->id())
            ->with(['prato:id,nome,descricao,imagem'])
            ->get();

        return response()->json([
            'favorites' => $favorites
        ]);
    }

    public function checkIsFavorite($pratoId)
    {
        $isFavorite = Favorite::where('user_id', auth()->id())
            ->where('prato_id', $pratoId)
            ->exists();

        return response()->json(['is_favorite' => $isFavorite]);
    }

    public function getMostFavoritedDishes()
    {
        $mostFavorited = Favorite::select('prato_id', DB::raw('count(*) as total_favorites'))
            ->with(['prato:id,nome,descricao,imagem']) // Carrega os dados do prato
            ->groupBy('prato_id')
            ->orderBy('total_favorites', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'prato' => $item->prato,
                    'total_favoritos' => $item->total_favorites
                ];
            });

        return response()->json([
            'most_favorited' => $mostFavorited
        ]);
    }
} 