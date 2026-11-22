<?php

namespace App\Http\Controllers;

use App\Models\ObjetoPerdido;
use Illuminate\Http\Request;

class ObjetoPerdidoController extends Controller
{
    public function index()
    {
        return response()->json(
            ObjetoPerdido::with('user')->orderByDesc('id')->limit(200)->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:180',
            'descripcion' => 'nullable|string|max:800',
            'tipo' => 'required|in:perdido,encontrado',
            'lugar_id' => 'nullable|string|max:80',
        ]);

        $data['user_id'] = $request->user()?->id;
        $item = ObjetoPerdido::create($data);

        return response()->json($item, 201);
    }

    public function destroy(Request $request, $id)
    {
        $item = ObjetoPerdido::find($id);
        if (!$item) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        $item->delete();
        return response()->json(['message' => 'Resuelto']);
    }
}
