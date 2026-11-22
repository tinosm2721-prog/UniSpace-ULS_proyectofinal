<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Certificacion;

class CertificacionController extends Controller
{
    /**
     * Listar certificaciones por perfil
     */
    public function index($id_perfil)
    {
        try {
        $cacheKey = "certificaciones_$id_perfil";
        $certificaciones = Cache::remember($cacheKey, 60, function () use ($id_perfil) {
            return DB::table('certificaciones')
                ->where('perfil_id', $id_perfil)
                ->get();
        });
        return response()->json($certificaciones);
        
        } catch (\Throwable $th) {
            return response()->json([
                'message'=>'Erro al obtener certificaciones'
            ]);
        }
    }

    /**
     * Crear certificación
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
            'certificacion' => 'required|string|max:200',
            'organizacion' => 'required|string|max:200',
            'fecha_emision' => 'nullable|date',
            'perfil_id' => 'required|integer',
        ]);
        $certificacion = Certificacion::create($data);
        Cache::forget("certificaciones_" . $data['perfil_id']);     

        return response()->json($certificacion, 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message'=>'Error al agregar',
                 'error'=> $th->getMessage(),
                 'line'=>$th->getLine()
            ],500);
        }
    }

    /**
     * Mostrar certificación
     */
    public function show($id)
    {
       try {
         $certificacion = DB::table('certificaciones')
            ->where('id_certificacion', $id)
            ->first();

        if (!$certificacion) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
        return response()->json($certificacion);

       } catch (\Throwable $th) {
        return response()->json([
            'menssage'=>'Error al mostrar'
        ],500);
       }
    }

    /**
     * Actualizar certificación
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->validate([
            'certificacion' => 'sometimes|string|max:200',
            'organizacion' => 'sometimes|string|max:200',
            'fecha_emision' => 'nullable|date'
        ]);
        $certificacion = Certificacion::find($id);

        $certificacion->update($data);
        Cache::forget("certificaciones_" . $certificacion->id_perfil);
        return response()->json($certificacion);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

    }

    /**
     * Eliminar certificación
     */
    public function destroy($id)
    {
    try {
        $certificacion = Certificacion::find($id);
        $id_perfil = $certificacion->id_perfil;
        $certificacion->delete();
        Cache::forget("certificaciones_$id_perfil");

        return response()->json(['message' => 'Eliminado']);
    } catch (\Throwable $th) {
                if (!$certificacion) {
            return response()->json(['message' => 'No encontrado'], 404);
        }
    }
    }
}
