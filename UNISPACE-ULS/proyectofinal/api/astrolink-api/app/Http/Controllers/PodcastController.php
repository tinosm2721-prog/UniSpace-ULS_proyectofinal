<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PodcastController extends Controller
{
    // =========================================
    // OBTENER TODOS LOS PODCAST
    // =========================================
    public function index()
    {
        try {

            $perPage = request()->get('per_page', 10);

            $podcasts = Podcast::with([
                    'user',
                    'likes',
                    'comentarios'
                ])
                ->orderByDesc('id')
                ->paginate($perPage);

            $userId = auth()->id();

            $podcasts->getCollection()->transform(function ($podcast) use ($userId) {

                $podcast->user_liked =
                    $podcast->likes->contains('user_id', $userId);

                $podcast->total_likes =
                    $podcast->likes->count();

                $podcast->total_comentarios =
                    $podcast->comentarios->count();

                $podcast->autor = trim(
                    ($podcast->user->nombre ?? '') . ' ' .
                    ($podcast->user->apellido ?? '')
                );

                return $podcast;
            });

            return response()->json($podcasts);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al obtener podcasts',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // OBTENER PODCAST POR ID
    // =========================================
    public function show($id)
    {
        try {

            $podcast = Podcast::with([
                    'user',
                    'likes',
                    'comentarios'
                ])
                ->find($id);

            if (!$podcast) {

                return response()->json([
                    'message' => 'Podcast no encontrado'
                ], 404);
            }

            $userId = auth()->id();

            $podcast->user_liked =
                $podcast->likes->contains('user_id', $userId);

            $podcast->total_likes =
                $podcast->likes->count();

            $podcast->total_comentarios =
                $podcast->comentarios->count();

            $podcast->autor = trim(
                ($podcast->user->nombre ?? '') . ' ' .
                ($podcast->user->apellido ?? '')
            );

            return response()->json($podcast);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al obtener podcast',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // PODCAST POR AUTOR
    // =========================================
    public function porAutor($userId)
    {
        try {

            $cacheKey = "podcast_autor_$userId";

            $podcasts = Cache::remember(
                $cacheKey,
                60,
                function () use ($userId) {

                    return Podcast::with([
                            'user',
                            'likes',
                            'comentarios'
                        ])
                        ->where('user_id', $userId)
                        ->orderByDesc('id')
                        ->get();
                }
            );

            $authUserId = auth()->id();

            $podcasts = $podcasts->map(function ($podcast) use ($authUserId) {

                $podcast->user_liked =
                    $podcast->likes->contains('user_id', $authUserId);

                $podcast->total_likes =
                    $podcast->likes->count();

                $podcast->total_comentarios =
                    $podcast->comentarios->count();

                $podcast->autor = trim(
                    ($podcast->user->nombre ?? '') . ' ' .
                    ($podcast->user->apellido ?? '')
                );

                return $podcast;
            });

            return response()->json($podcasts);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al obtener podcasts del autor',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // MIS PODCAST
    // =========================================
    public function mePodcast(Request $request)
    {
        $userId = $request->user()->id;

        try {

            $cacheKey = "podcast_autor_$userId";

            $podcasts = Cache::remember(
                $cacheKey,
                60,
                function () use ($userId) {

                    return Podcast::with([
                            'user',
                            'likes',
                            'comentarios'
                        ])
                        ->where('user_id', $userId)
                        ->orderByDesc('id')
                        ->get();
                }
            );

            $authUserId = auth()->id();

            $podcasts = $podcasts->map(function ($podcast) use ($authUserId) {

                $podcast->user_liked =
                    $podcast->likes->contains('user_id', $authUserId);

                $podcast->total_likes =
                    $podcast->likes->count();

                $podcast->total_comentarios =
                    $podcast->comentarios->count();

                $podcast->autor = trim(
                    ($podcast->user->nombre ?? '') . ' ' .
                    ($podcast->user->apellido ?? '')
                );

                return $podcast;
            });

            return response()->json($podcasts);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al obtener tus podcasts',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // PODCAST POR TEMA
    // =========================================
    public function porTema($tema)
    {
        try {

            $podcasts = Podcast::with([
                    'user',
                    'likes',
                    'comentarios'
                ])
                ->where('tema', $tema)
                ->orderByDesc('id')
                ->get();

            if ($podcasts->isEmpty()) {

                return response()->json([
                    'message' => 'No hay podcasts para este tema'
                ], 404);
            }

            $userId = auth()->id();

            $podcasts = $podcasts->map(function ($podcast) use ($userId) {

                $podcast->user_liked =
                    $podcast->likes->contains('user_id', $userId);

                $podcast->total_likes =
                    $podcast->likes->count();

                $podcast->total_comentarios =
                    $podcast->comentarios->count();

                $podcast->autor = trim(
                    ($podcast->user->nombre ?? '') . ' ' .
                    ($podcast->user->apellido ?? '')
                );

                return $podcast;
            });

            return response()->json($podcasts);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al obtener podcasts',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // CREAR PODCAST
    // =========================================
    public function store(Request $request)
    {
        try {

            $data = $request->validate([
                'tema' => 'required|string|max:200',
                'titulo' => 'required|string|max:200',
                'descripcion' => 'nullable|string',
                'contenido' => $request->hasFile('contenido') ? 'required|file|mimes:mp3,wav,ogg,mp4,mov,avi|max:512000' : 'required|url|max:2048',
            ]);

            $data['user_id'] =
                $request->user()->id;

            // guardar archivo
            if ($request->hasFile('contenido')) {

                $ruta = $request
                    ->file('contenido')
                    ->store('podcasts', 'public');

                $data['contenido'] = $ruta;
            }

            $podcast = Podcast::create($data);

            Cache::forget('feed_podcast');
            Cache::forget(
                "podcast_autor_" . $data['user_id']
            );

            return response()->json([
                'message' => 'Podcast creado',
                'data' => $podcast
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al crear podcast',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // ACTUALIZAR PODCAST
    // =========================================
        public function update(Request $request, $id)
    {
        try {

            $podcast = Podcast::find($id);

            if (!$podcast) {
                return response()->json(['message' => 'Podcast no encontrado'], 404);
            }

            if ($podcast->user_id !== $request->user()->id) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            $data = $request->validate([
                'tema' => 'sometimes|string|max:200',
                'titulo' => 'sometimes|string|max:200',
                'descripcion' => 'nullable|string',
                'contenido' => $request->hasFile('contenido')
                    ? 'nullable|file|mimes:mp3,wav,ogg,mp4,mov,avi|max:512000'
                    : 'nullable|url|starts_with:http://,https://|max:2048',
            ]);

            $anterior = $podcast->getRawOriginal('contenido');

            if ($request->hasFile('contenido')) {

                $this->borrarArchivo($anterior);

                $data['contenido'] = $request
                    ->file('contenido')
                    ->store('podcasts', 'public');

            } elseif (!empty($data['contenido'])) {

                // cambió a un enlace: se borra el archivo viejo si lo había
                $this->borrarArchivo($anterior);

            } else {

                // sin contenido nuevo: se conserva el actual
                unset($data['contenido']);
            }

            $podcast->update($data);

            Cache::forget('feed_podcast');
            Cache::forget("podcast_autor_" . $podcast->user_id);

            return response()->json([
                'message' => 'Podcast actualizado',
                'data' => $podcast
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'message' => 'Error al validar datos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al actualizar podcast',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    // =========================================
    // ELIMINAR PODCAST
    // =========================================
    public function destroy(Request $request, $id)
    {
        try {

            $podcast = Podcast::find($id);

            if (!$podcast) {

                return response()->json([
                    'message' => 'Podcast no encontrado'
                ], 404);
            }

            if (
                $podcast->user_id !==
                $request->user()->id
            ) {

                return response()->json([
                    'message' => 'No autorizado'
                ], 403);
            }

            // eliminar archivo físico
            $this->borrarArchivo($podcast->getRawOriginal('contenido'));

            $userId = $podcast->user_id;

            $podcast->delete();

            Cache::forget('feed_podcast');
            Cache::forget(
                "podcast_autor_$userId"
            );

            return response()->json([
                'message' => 'Podcast eliminado'
            ]);

        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Error al eliminar podcast',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    private function borrarArchivo($contenido): void
    {
        if ($contenido && !preg_match('#^https?://#i', $contenido)) {
            Storage::disk('public')->delete($contenido);
        }
    }
}