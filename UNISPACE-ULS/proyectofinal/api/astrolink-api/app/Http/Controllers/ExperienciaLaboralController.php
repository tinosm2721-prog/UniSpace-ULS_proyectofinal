<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\ExperienciaLaboral;

class ExperienciaLaboralController extends Controller
{
    /**
     * Listar experiencias
     */
    public function index(Request $request)
    {
        try {
            $perfilId = $this->getPerfilId($request);

            if (!$perfilId) {
                return response()->json([
                    'message' => 'Perfil no encontrado'
                ], 404);
            }

            $data = Cache::remember("exp_$perfilId", 300, function () use ($perfilId) {
                return ExperienciaLaboral::where('perfil_id', $perfilId)
                    ->orderByDesc('fecha_inicio')
                    ->get();
            });

            return response()->json($data);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener experiencias laborales'
            ], 500);
        }
    }

    /**
     * Crear experiencia
     */
    public function store(Request $request)
    {
        try {
            $perfilId = $this->getPerfilId($request);

            if (!$perfilId) {
                return response()->json([
                    'message' => 'Perfil no encontrado'
                ], 404);
            }

            $data = $request->validate([
                'nombre_empresa' => 'required|string|max:200',
                'descripcion_puesto' => 'required|string|max:500',
                'direccion' => 'nullable|string|max:200',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'nullable|date',
            ]);

            $data['perfil_id'] = $perfilId;

            $experiencia = ExperienciaLaboral::create($data);

            Cache::forget("exp_$perfilId");

            return response()->json([
                'message' => 'Experiencia creada',
                'data' => $experiencia
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear experiencia laboral'
                ,'error'=> $th->getMessage()
                
                ], 500);
        }
    }

    /**
     * Actualizar experiencia
     */
    public function update(Request $request, $id)
    {
        try {
            $perfilId = $this->getPerfilId($request);

            $experiencia = ExperienciaLaboral::where('id', $id)
                ->where('perfil_id', $perfilId)
                ->first();

            if (!$experiencia) {
                return response()->json([
                    'message' => 'Experiencia no encontrada'
                ], 404);
            }

            $data = $request->validate([
                'nombre_empresa' => 'sometimes|string|max:200',
                'descripcion_puesto' => 'sometimes|string|max:500',
                'direccion' => 'nullable|string|max:200',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);

            $experiencia->update($data);

            Cache::forget("exp_$perfilId");

            return response()->json([
                'message' => 'Experiencia actualizada',
                'data' => $experiencia
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar experiencia laboral'
            ], 500);
        }
    }

    /**
     * Eliminar experiencia
     */
    public function destroy(Request $request, $id)
    {
        try {
            $perfilId = $this->getPerfilId($request);

            $experiencia = ExperienciaLaboral::where('id', $id)
                ->where('perfil_id', $perfilId)
                ->first();

            if (!$experiencia) {
                return response()->json([
                    'message' => 'Experiencia no encontrada'
                ], 404);
            }

            $experiencia->delete();

            Cache::forget("exp_$perfilId");

            return response()->json([
                'message' => 'Experiencia eliminada'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar experiencia laboral'
            ], 500);
        }
    }

    /**
     * Obtener perfil del usuario logueado
     */
    private function getPerfilId(Request $request)
    {
        return DB::table('perfiles')
            ->where('user_id', $request->user()->id)
            ->value('id');
    }
}