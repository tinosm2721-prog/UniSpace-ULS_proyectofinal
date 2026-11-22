<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Proyecto;

class ProyectoController extends Controller
{
    public function index($perfilId)
    {
        try {
            $cacheKey = "proyectos_$perfilId";

            $proyectos = Cache::remember($cacheKey, 60, function () use ($perfilId) {
                return Proyecto::where('perfil_id', $perfilId)
                    ->orderByDesc('fecha_inicio')
                    ->get();
            });

            return response()->json($proyectos);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener proyectos'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'perfil_id' => 'required|integer',
                'proyecto' => 'required|string|max:200',
                'descripcion' => 'nullable|string',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
                'url_proyecto' => 'nullable|url|max:500',
            ]);

            $proyecto = Proyecto::create($data);

            Cache::forget("proyectos_" . $data['perfil_id']);

            return response()->json([
                'message' => 'Proyecto creado',
                'data' => $proyecto
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear proyecto'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $proyecto = Proyecto::find($id);

            if (!$proyecto) {
                return response()->json([
                    'message' => 'Proyecto no encontrado'
                ], 404);
            }

            return response()->json($proyecto);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener proyecto'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $proyecto = Proyecto::find($id);

            if (!$proyecto) {
                return response()->json([
                    'message' => 'Proyecto no encontrado'
                ], 404);
            }

            $data = $request->validate([
                'proyecto' => 'sometimes|string|max:200',
                'descripcion' => 'nullable|string',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
                'url_proyecto' => 'nullable|url|max:500',
            ]);

            $proyecto->update($data);

            Cache::forget("proyectos_" . $proyecto->perfil_id);

            return response()->json([
                'message' => 'Proyecto actualizado',
                'data' => $proyecto
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar proyecto'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $proyecto = Proyecto::find($id);

            if (!$proyecto) {
                return response()->json([
                    'message' => 'Proyecto no encontrado'
                ], 404);
            }

            $perfilId = $proyecto->perfil_id;

            $proyecto->delete();

            Cache::forget("proyectos_$perfilId");

            return response()->json([
                'message' => 'Proyecto eliminado'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar proyecto'
            ], 500);
        }
    }
}