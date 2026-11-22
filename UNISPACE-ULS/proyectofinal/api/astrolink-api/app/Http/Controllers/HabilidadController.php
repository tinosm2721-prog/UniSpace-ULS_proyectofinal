<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Habilidad;

class HabilidadController extends Controller
{
    /**
     * Listar habilidades por perfil
     */
    public function index($perfilId)
    {
        try {
            $cacheKey = "habilidades_$perfilId";

            $habilidades = Cache::remember($cacheKey, 60, function () use ($perfilId) {
                return Habilidad::where('perfil_id', $perfilId)
                    ->orderBy('id', 'desc')
                    ->get();
            });

            return response()->json($habilidades);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener habilidades'
            ], 500);
        }
    }

    /**
     * Crear habilidad
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'perfil_id' => 'required|integer',
                'habilidad' => 'required|string|max:100',
            ]);

            $habilidad = Habilidad::create($data);

            Cache::forget("habilidades_" . $data['perfil_id']);

            return response()->json([
                'message' => 'Habilidad creada',
                'data' => $habilidad
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear habilidad'
            ], 500);
        }
    }

    /**
     * Mostrar una habilidad
     */
    public function show($id)
    {
        try {
            $habilidad = Habilidad::find($id);

            if (!$habilidad) {
                return response()->json([
                    'message' => 'Habilidad no encontrada'
                ], 404);
            }

            return response()->json($habilidad);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener habilidad'
            ], 500);
        }
    }

    /**
     * Actualizar habilidad
     */
    public function update(Request $request, $id)
    {
        try {
            $habilidad = Habilidad::find($id);

            if (!$habilidad) {
                return response()->json([
                    'message' => 'Habilidad no encontrada'
                ], 404);
            }

            $data = $request->validate([
                'habilidad' => 'sometimes|string|max:100',
            ]);

            $habilidad->update($data);

            Cache::forget("habilidades_" . $habilidad->perfil_id);

            return response()->json([
                'message' => 'Habilidad actualizada',
                'data' => $habilidad
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar habilidad'
            ], 500);
        }
    }

    /**
     * Eliminar habilidad
     */
    public function destroy($id)
    {
        try {
            $habilidad = Habilidad::find($id);

            if (!$habilidad) {
                return response()->json([
                    'message' => 'Habilidad no encontrada'
                ], 404);
            }

            $perfilId = $habilidad->perfil_id;

            $habilidad->delete();

            Cache::forget("habilidades_$perfilId");

            return response()->json([
                'message' => 'Habilidad eliminada'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar habilidad'
            ], 500);
        }
    }
}