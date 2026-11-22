<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Idioma;

class IdiomaController extends Controller
{
    public function index($perfilId)
        {
            try {
                $idiomas = Idioma::where('perfil_id',$perfilId)->orderBy('id', 'desc')
                        ->get();

                return response()->json($idiomas);

            } catch (\Throwable $th) {
                return response()->json([
                    'message' => 'Error al obtener idiomas',
                    'error' => $th->getMessage(),
                    'line' => $th->getLine(),
                    'file' => $th->getFile()
                ], 500);
            }
        }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([

                'idioma' => 'required|string|max:100',
                 'nivel' => 'required|string|max:100',
                  'perfil_id' => 'required|integer',
            ]);

            $idioma = Idioma::create($data);

            return response()->json([
                'message' => 'Idioma creado',
                'data' => $idioma
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear idioma'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $idioma = Idioma::find($id);

            if (!$idioma) {
                return response()->json([
                    'message' => 'Idioma no encontrado'
                ], 404);
            }

            return response()->json($idioma);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener idioma'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $idioma = Idioma::find($id);

            if (!$idioma) {
                return response()->json([
                    'message' => 'Idioma no encontrado'
                ], 404);
            }

            $data = $request->validate([
                'idioma' => 'sometimes|string|max:100',
            ]);

            $idioma->update($data);

            return response()->json([
                'message' => 'Idioma actualizado',
                'data' => $idioma
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar idioma'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $idioma = Idioma::find($id);

            if (!$idioma) {
                return response()->json([
                    'message' => 'Idioma no encontrado'
                ], 404);
            }

            $idioma->delete();

            return response()->json([
                'message' => 'Idioma eliminado'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar idioma'
            ], 500);
        }
    }
}