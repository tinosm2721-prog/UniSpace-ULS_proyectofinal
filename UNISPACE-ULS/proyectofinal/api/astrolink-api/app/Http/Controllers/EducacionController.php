<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Educacion;

class EducacionController extends Controller
{
    /**
     * Listar educación del usuario
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

            $data = Cache::remember("edu_$perfilId", 300, function () use ($perfilId) {
                return Educacion::where('perfil_id', $perfilId)
                    ->orderByDesc('fecha_inicio')
                    ->get();
            });

            return response()->json($data);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener educación',
                'error'=>$th->getMessage(),
                'line'=>$th->getLine()
            ], 500);
        }
    }

    /**
     * Crear registro educación
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
                'nombre_institucion' => 'required|string|max:200',
                'titulo' => 'required|string|max:150',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);

            $data['perfil_id'] = $perfilId;

            $educacion = Educacion::create($data);

            Cache::forget("edu_$perfilId");

            return response()->json([
                'message' => 'Educación creada',
                'data' => $educacion
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear educación',
                'error'=>$th->getMessage(),
                'line'=>$th->getLine(),
                 'file'=> $th->getFile()
            ], 500);
        }
    }

    /**
     * Actualizar
     */
    public function update(Request $request, $id)
    {
        try {
            $perfilId = $this->getPerfilId($request);

            $educacion = Educacion::where('id', $id)
                ->where('perfil_id', $perfilId)
                ->first();

            if (!$educacion) {
                return response()->json([
                    'message' => 'Educación no encontrada'
                ], 404);
            }

            $data = $request->validate([
                'nombre_institucion' => 'sometimes|string|max:200',
                'titulo' => 'sometimes|string|max:150',
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date',
            ]);

            $educacion->update($data);

            Cache::forget("edu_$perfilId");

            return response()->json([
                'message' => 'Educación actualizada',
                'data' => $educacion
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar educación'
            ], 500);
        }
    }

    /**
     * Eliminar
     */
    public function destroy(Request $request, $id)
    {
        try {
            $perfilId = $this->getPerfilId($request);

            $educacion = Educacion::where('id', $id)
                ->where('perfil_id', $perfilId)
                ->first();

            if (!$educacion) {
                return response()->json([
                    'message' => 'Educación no encontrada'
                ], 404);
            }

            $educacion->delete();

            Cache::forget("edu_$perfilId");

            return response()->json([
                'message' => 'Educación eliminada'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar educación'
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