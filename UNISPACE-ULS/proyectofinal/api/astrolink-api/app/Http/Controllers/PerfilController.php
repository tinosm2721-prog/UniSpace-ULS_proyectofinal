<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PerfilController extends Controller
{
    public function me(Request $request)
    {
        try {
           $perfil = Perfil::with([
                'user',
                'idioma',
                'educacion',
                'certificacion',
                'experienciaLaboral',
                'habilidad',
                'proyecto'
            ])->where('user_id', $request->user()->id)->first();
            if (!$perfil) {
                return response()->json([
                    'message' => 'Perfil no encontrado'
                ], 404);
            }

            return response()->json($perfil);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener perfil'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if (Perfil::where('user_id', $request->user()->id)->exists()) {
                return response()->json([
                    'message' => 'El usuario ya tiene perfil'
                ], 409);
            }

            $data = $request->validate([
                'titulo' => 'nullable|string|max:220',
                'sobre_mi' => 'nullable|string',
                'direccion' => 'nullable|string|max:200',
                'telefono' => 'nullable|string|max:20',
                'url_sitio_web' => 'nullable|url|max:255',
                'url_linkedin' => 'nullable|url|max:255',
                'url_github' => 'nullable|url|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'foto_perfil' => 'nullable|image|max:2048',
                'imagen_portada' => 'nullable|image|max:4096',
            ]);

            $data['user_id'] = $request->user()->id;

            $perfil = Perfil::create($data);

            if ($request->hasFile('foto_perfil')) {
                $perfil->uploadImage($request->file('foto_perfil'));
            }

            if ($request->hasFile('imagen_portada')) {
                $file = $request->file('imagen_portada');
                $filename = 'portada_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('perfiles/portadas', $filename, 'public');

                $perfil->update([
                    'imagen_portada' => $path
                ]);
            }

            return response()->json([
                'message' => 'Perfil creado',
                'perfil' => $perfil->fresh()
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear perfil'
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $perfil = Perfil::where('user_id', $request->user()->id)->first();

            if (!$perfil) {
                return response()->json([
                    'message' => 'Perfil no encontrado'
                ], 404);
            }

            $data = $request->validate([
                'titulo' => 'sometimes|string|max:220',
                'sobre_mi' => 'nullable|string',
                'direccion' => 'nullable|string|max:200',
                'telefono' => 'nullable|string|max:20',
                'url_sitio_web' => 'nullable|url|max:255',
                'url_linkedin' => 'nullable|url|max:255',
                'url_github' => 'nullable|url|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'foto_perfil' => 'nullable|image|max:6048',
                'imagen_portada' => 'nullable|image|max:6096',
            ]);

            $perfil->update($data);

            if ($request->hasFile('foto_perfil')) {
                $perfil->uploadImage($request->file('foto_perfil'));
            }

            if ($request->hasFile('imagen_portada')) {

                if ($perfil->imagen_portada && Storage::disk('public')->exists($perfil->imagen_portada)) {
                    Storage::disk('public')->delete($perfil->imagen_portada);
                }

                $file = $request->file('imagen_portada');
                $filename = 'portada_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('perfiles/portadas', $filename, 'public');

                $perfil->update([
                    'imagen_portada' => $path
                ]);
            }

            return response()->json([
                'message' => 'Perfil actualizado',
                'perfil' => $perfil->fresh()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al actualizar perfil'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $perfil = Perfil::with([
                'user',
                'idioma',
                'educacion',
                'certificacion',
                'experienciaLaboral',
                'habilidad',
                'proyecto'
            ])
            ->where('user_id', $id)
            ->first();

            if (!$perfil) {
                return response()->json([
                    'message' => 'Perfil no encontrado'
                ], 404);
            }

            return response()->json($perfil);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener perfil'
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $perfil = Perfil::where('user_id', $request->user()->id)->first();

            if (!$perfil) {
                return response()->json([
                    'message' => 'Perfil no encontrado'
                ], 404);
            }

            if ($perfil->foto_perfil && Storage::disk('public')->exists($perfil->foto_perfil)) {
                Storage::disk('public')->delete($perfil->foto_perfil);
            }

            if ($perfil->imagen_portada && Storage::disk('public')->exists($perfil->imagen_portada)) {
                Storage::disk('public')->delete($perfil->imagen_portada);
            }

            $perfil->delete();

            return response()->json([
                'message' => 'Perfil eliminado'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar perfil'
            ], 500);
        }
    }
}