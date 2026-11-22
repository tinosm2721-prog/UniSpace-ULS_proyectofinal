<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Comentario;
use App\Models\Publicacion;
use Illuminate\Database\Eloquent\Relations\Relation;

class ComentarioController extends Controller
{
    /**
     * Listar comentarios (polimórfico)
     */
public function index(Request $request)
{
    $commentable_id = $request->commentable_id;

    $commentable_type = $request->commentable_type;
    

    $limit = min($request->input('limit', 10), 50);

    $offset = $request->input('offset', 0);

    $total = Comentario::where(
        'commentable_id',
        $commentable_id
    )
    ->where(
        'commentable_type',
        $commentable_type
    )
    ->count();

    $comentarios = Comentario::with('user')
        ->where(
            'commentable_id',
            $commentable_id
        )
        ->where(
            'commentable_type',
            $commentable_type
        )
        ->orderBy('id', 'desc')
        ->limit($limit)
        ->offset($offset)
        ->get();

    return response()->json([
        'comentarios' => $comentarios ,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'hasMore' => ($offset + $limit) < $total
    ]);
}
    /**
     * Crear comentario
     */
public function store(Request $request)
{
    try {
        $data = $request->validate([
            'comentario' => 'required|string',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string', // "publicacion"
        ]);

        // 🔥 convertir tipo
        $modelClass = Relation::getMorphedModel($data['commentable_type']);

        if (!$modelClass) {
            return response()->json([
                'message' => 'Tipo inválido'
            ], 400);
        }

        // 🔍 buscar modelo
        $modelo = $modelClass::findOrFail($data['commentable_id']);

                    // ✅ crear comentario (FORMA PRO)
            $comentario = $modelo->comentarios()->create([
                'comentario' => $data['comentario'],
                'user_id' => $request->user()->id
            ]);

            $comentario->load('user');

            return response()->json([
                'message' => 'Comentario creado',
                'data' => $comentario
            ], 201);

    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Error al comentar',
            'error' => $th->getMessage()
        ], 500);
    }
}

    /**
     * Mostrar comentario
     */
    public function show($id)
    {
        $comentario = Comentario::with('user')
            ->find($id);

        if (!$comentario) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        return response()->json($comentario);
    }

    /**
     * Actualizar comentario
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'comentario' => 'required|string|max:5000',
        ]);

        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        if ($comentario->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $comentario->update($data);

        Cache::forget("comentarios_" . $comentario->commentable_id);

        return response()->json($comentario->load('user'));
    }

    /**
     * Eliminar comentario
     */
    public function destroy($id)
    {
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        if ($comentario->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $commentableId = $comentario->commentable_id;

        $comentario->delete();

        Cache::forget("comentarios_" . $commentableId);

        return response()->json(['message' => 'Eliminado']);
    }
}