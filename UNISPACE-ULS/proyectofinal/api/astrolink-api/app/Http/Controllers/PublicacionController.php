<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Publicacion;
use Illuminate\Support\Facades\Storage;

class PublicacionController extends Controller
{
public function index()
{
    try {

        $perPage = request()->get('per_page', 10);

        $publicaciones = Publicacion::with([
                'user',
                'likes',
                'comentarios'
            ])
            ->where('publica', true)
            ->orderByDesc('id')
            ->paginate($perPage);

        $userId = auth()->id();

        $publicaciones->getCollection()->transform(function ($pub) use ($userId) {

            $pub->user_liked = $pub->likes->contains('user_id', $userId);

            $pub->total_likes = $pub->likes->count();

            $pub->total_comentarios = $pub->comentarios->count();

            $pub->autor = trim(
                ($pub->user->nombre ?? '') . ' ' . ($pub->user->apellido ?? '')
            );

            return $pub;
        });

        return response()->json($publicaciones);

    } catch (\Throwable $th) {

        return response()->json([
            'message' => 'Error al obtener publicaciones',
            'error' => $th->getMessage()
        ], 500);
    }
}

public function show($id)
{
    try {
        $publicacion = Publicacion::with(['user', 'likes', 'comentarios'])->find($id);

        if (!$publicacion) {
            return response()->json([
                'message' => 'Publicación no encontrada'
            ], 404);
        }

        $userId = auth()->id();

        $publicacion->user_liked = $publicacion->likes->contains('user_id', $userId);
        $publicacion->total_likes = $publicacion->likes->count();
        $publicacion->total_comentarios = $publicacion->comentarios->count();
                    $publicacion->autor =
                trim(
                    ($publicacion->user->nombre ?? '') . ' ' .
                    ($publicacion->user->apellido ?? '')
                );
        

        return response()->json($publicacion);

    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Error al obtener publicación'
        ], 500);
    }
}

public function porAutor($userId)
{
    try {
        $cacheKey = "publicaciones_autor_$userId";

        $publicaciones = Cache::remember($cacheKey, 60, function () use ($userId) {
            return Publicacion::with(['likes', 'comentarios'])
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->get();
        });

        $authUserId = auth()->id();

        $publicaciones = $publicaciones->map(function ($pub) use ($authUserId) {
            $pub->user_liked = $pub->likes->contains('user_id', $authUserId);
            $pub->total_likes = $pub->likes->count();
            $pub->total_comentarios = $pub->comentarios->count();
            return $pub;
        });

        return response()->json($publicaciones);

    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Error al obtener publicaciones del autor'
        ], 500);
    }
}

public function porTema($tema)
{
    try {

        $publicaciones = Publicacion::with([
                'user',
                'likes',
                'comentarios'
            ])
            ->where('tema', $tema)
            ->where('publica', true)
            ->orderByDesc('id')
            ->get();

        if ($publicaciones->isEmpty()) {

            return response()->json([
                'message' => 'No hay publicaciones para este tema'
            ], 404);
        }

        $userId = auth()->id();

        $publicaciones = $publicaciones->map(function ($pub) use ($userId) {

            $pub->user_liked =
                $pub->likes->contains('user_id', $userId);

            $pub->total_likes =
                $pub->likes->count();

            $pub->total_comentarios =
                $pub->comentarios->count();

            $pub->autor =
                trim(
                    ($pub->user->nombre ?? '') . ' ' .
                    ($pub->user->apellido ?? '')
                );

            return $pub;
        });

        return response()->json($publicaciones);

    } catch (\Throwable $th) {

        return response()->json([
            'message' => 'Error al obtener publicaciones'
        ], 500);
    }
}

public function mePublicacion(Request $request)
{
    
    $userId= $request->user()->id;
    try {
        $cacheKey = "publicaciones_autor_$userId";

        $publicaciones = Cache::remember($cacheKey, 60, function () use ($userId) {
            return Publicacion::with(['likes', 'comentarios'])
                ->where('user_id', $userId)
                ->orderByDesc('id')
                ->get();
        });

        $authUserId = auth()->id();

        $publicaciones = $publicaciones->map(function ($pub) use ($authUserId) {
            $pub->user_liked = $pub->likes->contains('user_id', $authUserId);
            $pub->total_likes = $pub->likes->count();
            $pub->total_comentarios = $pub->comentarios->count();
            return $pub;
        });

        return response()->json($publicaciones);

    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Error al obtener publicaciones del autor'
        ], 500);
    }
}


    public function store(Request $request)
    {
        try {
$data = $request->validate([
    'tema' => 'nullable|string|max:200',
    'titulo' => 'nullable|string|max:255',
    'contenido' => 'required|string',
    'imagen' => 'nullable|image|max:5000',
    'publica' => 'boolean',
    'ubicacion' => 'nullable|string|max:255',
    'aula' => 'nullable|string|max:255',
    'lugar_id' => 'nullable|string|max:255',
    'lat' => 'nullable|numeric',
    'lng' => 'nullable|numeric',
]);

            $data['user_id'] = $request->user()->id;

            // ✅ Guardar imagen
            if ($request->hasFile('imagen')) {
                $ruta = $request->file('imagen')->store('publicaciones', 'public');
                $data['imagen'] = $ruta; // 👈 guardas SOLO la ruta
            }

            $publicacion = Publicacion::create($data);

            Cache::forget('feed_publicaciones');
            Cache::forget("publicaciones_autor_" . $data['user_id']);

            return response()->json([
                'message' => 'Publicación creada',
                'data' => $publicacion // 👈 aquí ya aplica el accessor automáticamente
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al crear publicación',
                'error' => $th->getMessage()
            ], 500);
        }
    }

public function update(Request $request, $id)
{
    try {

        $publicacion = Publicacion::find($id);

        if (!$publicacion) {
            return response()->json([
                'message' => 'Publicación no encontrada'
            ], 404);
        }

        if ($publicacion->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'No autorizado'
            ], 403);
        }
        
$data = $request->validate([
    'tema' => 'sometimes|string|max:200',
    'titulo' => 'sometimes|string|max:255',
    'contenido' => 'sometimes|string',
    'imagen' => 'nullable|image|max:2048',
    'publica' => 'boolean',
    'ubicacion' => 'nullable|string|max:255',
    'aula' => 'nullable|string|max:255',
    'lugar_id' => 'nullable|string|max:255',
    'lat' => 'nullable|numeric',
    'lng' => 'nullable|numeric',
]);

        // SI VIENE NUEVA IMAGEN
        if ($request->hasFile('imagen')) {

            // ELIMINAR IMAGEN ANTERIOR
            if ($publicacion->imagen) {

                Storage::disk('public')->delete($publicacion->imagen);
            }

            // GUARDAR NUEVA IMAGEN
            $ruta = $request->file('imagen')->store('publicaciones', 'public');

            $data['imagen'] = $ruta;
        }

        $publicacion->update($data);

        Cache::forget('feed_publicaciones');
        Cache::forget("publicaciones_autor_" . $publicacion->user_id);

        return response()->json([
            'message' => 'Publicación actualizada',
            'data' => $publicacion
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {

        return response()->json([
            'message' => 'Error al validar datos',
            'errors' => $e->errors()
        ], 422);

    } catch (\Throwable $th) {

        return response()->json([
            'message' => 'Error al actualizar publicación',
            'error' => $th->getMessage()
        ], 500);
    }
}


    public function destroy(Request $request, $id)
    {
        try {
            $publicacion = Publicacion::find($id);

            if (!$publicacion) {
                return response()->json([
                    'message' => 'Publicación no encontrada'
                ], 404);
            }

            if ($publicacion->user_id !== $request->user()->id) {
                return response()->json([
                    'message' => 'No autorizado'
                ], 403);
            }

            $userId = $publicacion->user_id;

            $publicacion->delete();

            Cache::forget('feed_publicaciones');
            Cache::forget("publicaciones_autor_$userId");

            return response()->json([
                'message' => 'Publicación eliminada'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al eliminar publicación'
            ], 500);
        }
    }
}