<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */
public function register(Request $request)
{
    $validated = $request->validate([
        'nombre'     => 'required|string|max:100',
        'apellido'   => 'required|string|max:100',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'fecha_nacimiento' => 'required|date'
    ]);

    DB::beginTransaction();

    try {

        // Crear usuario
        $user = User::create([
            'nombre'  => $validated['nombre'],
            'apellido'  => $validated['apellido'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Crear perfil con fecha de nacimiento
        DB::table('perfiles')->insert([
            'user_id' => $user->id, // mismo id del user
            'fecha_nacimiento' => $validated['fecha_nacimiento'],
        ]);

        // Token
        $token = $user->createToken('auth_token')->plainTextToken;

        DB::commit();

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'apellido' => $user->apellido,
                
                'email' => $user->email,
            ],
            'token' => $token,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Error al registrar usuario',
            'error' => $e->getLine()

        ], 500);
    }
}


    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Query Builder = más rápido que Eloquent
        $user = DB::table('users')
            ->where('email', $request->email)
            ->where('esta_activo', true)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Convertir a modelo User para Sanctum
        $userModel = User::find($user->id);

        $token = $userModel->createToken('api_token')->plainTextToken;

        // Actualizar último login
        DB::table('users')
            ->where('id', $user->id)
            ->update(['ultimo_login' => now()]);

        // Cache usuario básico
        Cache::put("user:{$user->id}", [
            'id' => $user->id,
            'nombre' => $user->nombre,
            'email' => $user->email,
        ], 300);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'email' => $user->email,
            ],
            'token' => $token
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
            Cache::forget("user:{$user->id}");
        }

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ME
    |--------------------------------------------------------------------------
    */

public function me(Request $request)
{
    $user = $request->user();

    $cached = Cache::get("user:{$user->id}");

    if ($cached) {
        return response()->json($cached);
    }

    $data = User::with('perfil')
        ->select(
            'id',
            'nombre',
            'email',
            'esta_activo'
        )
        ->find($user->id);

    // Agregar URL completa de foto
    if ($data->perfil?->foto_perfil) {

        $data->perfil->foto_perfil_url =
            asset($data->perfil->foto_perfil);
    }

    Cache::put(
        "user:{$user->id}",
        $data,
        now()->addMinutes(5)
    );

    return response()->json($data);
}
}
