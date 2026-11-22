<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UsuarioController extends Controller
{
    public function index()
    {
        try {
            $usuarios = Cache::remember('usuarios_lista', 300, function () {
                return User::with('perfil')
                    ->where('esta_activo', true)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'nombre' => $user->nombre,
                            'apellido' => $user->apellido,
                            'email' => $user->email,
                            'foto_perfil' => $user->perfil->foto_perfil ?? null,
                            'titulo' => $user->perfil->titulo ?? null,
                        ];
                    });
            });

            return response()->json($usuarios);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener usuarios'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $usuario = User::with('perfil')->find($id);

            if (!$usuario) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email,
                'foto_perfil' => $user->perfil->foto_perfil ?? null,
                'titulo' => $user->perfil->titulo ?? null,
                   
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener usuario'
            ], 500);
        }
    }

    public function showPerfil($id)
    {
        try {
            $usuario = User::with('perfil')->find($id);

            if (!$usuario) {
                return response()->json([
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            return response()->json([
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apellido' => $usuario->apellido,
                'email' => $usuario->email,
                'perfil' => $usuario->perfil,
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al obtener perfil del usuario'
            ], 500);
        }
    }

    public function buscar(Request $request)
    {
        try {
            $query = $request->get('q', '');

            if (empty($query)) {
                return response()->json([]);
            }

            $usuarios = User::with('perfil')
                ->where('esta_activo', true)
                ->where(function ($q) use ($query) {
                    $q->where('nombre', 'LIKE', "%{$query}%")
                      ->orWhere('apellido', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->limit(10)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'nombre' => $user->nombre,
                        'apellido' => $user->apellido,
                        'email' => $user->email,
                        'foto_perfil' => $user->perfil->foto_perfil ?? null,
                        'titulo' => $user->perfil->titulo ?? null,
                    ];
                });

            return response()->json($usuarios);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Error al buscar usuarios'
            ], 500);
        }
    }
}