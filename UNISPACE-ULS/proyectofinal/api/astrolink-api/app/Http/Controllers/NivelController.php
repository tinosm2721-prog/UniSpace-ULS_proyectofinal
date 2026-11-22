<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function index()
    {
        try {
            $niveles = Nivel::all();

            return response()->json($niveles);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener niveles'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'nivel' => 'required|string|max:100',
            ]);

            $nivel = Nivel::create($data);

            return response()->json([
                'message' => 'Nivel creado',
                'data' => $nivel
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear nivel'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $nivel = Nivel::find($id);

            if (!$nivel) {
                return response()->json([
                    'message' => 'Nivel no encontrado'
                ], 404);
            }

            return response()->json($nivel);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener nivel'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $nivel = Nivel::find($id);

            if (!$nivel) {
                return response()->json([
                    'message' => 'Nivel no encontrado'
                ], 404);
            }

            $data = $request->validate([
                'nivel' => 'sometimes|string|max:100',
            ]);

            $nivel->update($data);

            return response()->json([
                'message' => 'Nivel actualizado',
                'data' => $nivel
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar nivel'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $nivel = Nivel::find($id);

            if (!$nivel) {
                return response()->json([
                    'message' => 'Nivel no encontrado'
                ], 404);
            }

            $nivel->delete();

            return response()->json([
                'message' => 'Nivel eliminado'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar nivel'
            ], 500);
        }
    }
}