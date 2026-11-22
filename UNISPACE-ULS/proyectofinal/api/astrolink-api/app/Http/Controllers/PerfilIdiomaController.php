<?php

namespace App\Http\Controllers;

use App\Models\PerfilIdioma;
use Illuminate\Http\Request;

class PerfilIdiomaController extends Controller
{
    public function index()
    {
        try {
            $data = PerfilIdioma::with(['perfil', 'idioma', 'nivel'])->get();

            return response()->json($data);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener perfil idiomas'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'perfil_id' => 'required|integer',
                'idioma_id' => 'required|integer',
                'nivel_id' => 'required|integer',
            ]);

            $perfilIdioma = PerfilIdioma::create($data);

            return response()->json([
                'message' => 'Idioma agregado al perfil',
                'data' => $perfilIdioma
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear perfil idioma'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $perfilIdioma = PerfilIdioma::with(['perfil', 'idioma', 'nivel'])->find($id);

            if (!$perfilIdioma) {
                return response()->json([
                    'message' => 'Registro no encontrado'
                ], 404);
            }

            return response()->json($perfilIdioma);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener perfil idioma'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $perfilIdioma = PerfilIdioma::find($id);

            if (!$perfilIdioma) {
                return response()->json([
                    'message' => 'Registro no encontrado'
                ], 404);
            }

            $data = $request->validate([
                'idioma_id' => 'sometimes|integer',
                'nivel_id' => 'sometimes|integer',
            ]);

            $perfilIdioma->update($data);

            return response()->json([
                'message' => 'Perfil idioma actualizado',
                'data' => $perfilIdioma
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar perfil idioma'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $perfilIdioma = PerfilIdioma::find($id);

            if (!$perfilIdioma) {
                return response()->json([
                    'message' => 'Registro no encontrado'
                ], 404);
            }

            $perfilIdioma->delete();

            return response()->json([
                'message' => 'Perfil idioma eliminado'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar perfil idioma'
            ], 500);
        }
    }
}