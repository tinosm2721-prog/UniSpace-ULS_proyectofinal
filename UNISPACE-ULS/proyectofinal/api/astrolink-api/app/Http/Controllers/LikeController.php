<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Like;
use App\Models\Publicacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\Relation;

class LikeController extends Controller
{
public function toggle(Request $request)
{
    try {
        $data = $request->validate([
            'likeable_id' => 'required|integer',
            'likeable_type' => 'required|string',
        ]);

        $modelClass = Relation::getMorphedModel($data['likeable_type']);

        if (!$modelClass) {
            return response()->json([
                'message' => 'Tipo inválido'
            ], 400);
        }

        $modelo = $modelClass::findOrFail($data['likeable_id']);
        $userId = $request->user()->id;

        $like = $modelo->likes()
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            try {
                $modelo->likes()->create([
                    'user_id' => $userId
                ]);
                $liked = true;
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == '23505') {
                    $liked = true;
                } else {
                    throw $e;
                }
            }
        }

        // Invalidar caché para que el próximo index() traiga datos frescos
        Cache::forget('feed_publicaciones');
        Cache::forget("publicaciones_autor_{$modelo->user_id}");

        return response()->json([
            'liked'       => $liked,
            'total_likes' => $modelo->likes()->count()
        ]);

    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Error al procesar like',
            'error'   => $th->getMessage()
        ], 500);
    }
}
    public function check($likeableId, $userId)
    {
        try {
            $liked = Like::where('user_id', $userId)
                ->where('likeable_id', $likeableId)
                ->exists();

            return response()->json([
                'liked' => $liked
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al verificar like'
            ], 500);
        }
    }

    public function count($likeableId)
    {
        try {
            $cacheKey = "likes_$likeableId";

            $total = Cache::remember($cacheKey, 60, function () use ($likeableId) {
                return Like::where('likeable_id', $likeableId)->count();
            });

            return response()->json([
                'total_likes' => $total
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al contar likes'
            ], 500);
        }
    }
}