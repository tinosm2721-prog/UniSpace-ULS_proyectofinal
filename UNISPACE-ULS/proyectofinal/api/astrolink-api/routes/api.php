<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ExperienciaLaboralController;
use App\Http\Controllers\EducacionController;
use App\Http\Controllers\HabilidadController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\CertificacionController;
use App\Http\Controllers\IdiomaController;
use App\Http\Controllers\PublicacionController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\PerfilIdiomaController;

/*
|--------------------------------------------------------------------------
| Rutas públicas
|--------------------------------------------------------------------------
*/
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
        Route::get('/user', [UsuarioController::class, 'index']);

// TEMPORAL: diagnóstico. Eliminar después de corregir el error de login.
Route::get('/_diagnostico', function (\Illuminate\Http\Request $request) {
    $clave = env('DEBUG_KEY');
    if (!$clave || !hash_equals($clave, (string) $request->header('X-Debug-Key'))) {
        abort(404);
    }
    $r = [];
    try { \Illuminate\Support\Facades\DB::select('select 1'); $r['conexion_db'] = 'ok'; }
    catch (\Throwable $e) { $r['conexion_db'] = 'ERROR: ' . $e->getMessage(); }
    foreach (['users', 'personal_access_tokens', 'cache', 'sessions', 'perfiles', 'jobs'] as $t) {
        try { $r['tabla_' . $t] = \Illuminate\Support\Facades\Schema::hasTable($t) ? 'existe' : 'NO existe'; }
        catch (\Throwable $e) { $r['tabla_' . $t] = 'ERROR: ' . $e->getMessage(); }
    }
    foreach (['esta_activo', 'ultimo_login'] as $c) {
        try { $r['columna_users_' . $c] = \Illuminate\Support\Facades\Schema::hasColumn('users', $c) ? 'existe' : 'NO existe'; }
        catch (\Throwable $e) { $r['columna_users_' . $c] = 'ERROR: ' . $e->getMessage(); }
    }
    try { \Illuminate\Support\Facades\Cache::put('diag', 1, 60); $r['cache_escritura'] = 'ok'; }
    catch (\Throwable $e) { $r['cache_escritura'] = 'ERROR: ' . $e->getMessage(); }
    try {
        $u = \Illuminate\Support\Facades\DB::table('users')->where('email', 'x@x.com')->first();
        $r['consulta_users'] = 'ok';
    } catch (\Throwable $e) { $r['consulta_users'] = 'ERROR: ' . $e->getMessage(); }
    try {
        $req = \Illuminate\Http\Request::create('/api/auth/login', 'POST', ['email' => 'x@x.com', 'password' => 'x']);
        $req->headers->set('Accept', 'application/json');
        $resp = app(AuthController::class)->login($req);
        $r['login_resultado'] = $resp->getStatusCode();
    } catch (\Throwable $e) {
        $r['login_error'] = get_class($e) . ': ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine();
    }
    // Prueba de registro dentro de una transacción que SIEMPRE se revierte (no guarda nada)
    \Illuminate\Support\Facades\DB::beginTransaction();
    try {
        $u = \App\Models\User::create([
            'nombre' => 'diag', 'apellido' => 'diag',
            'email' => 'diag-' . uniqid() . '@test.invalid',
            'password' => \Illuminate\Support\Facades\Hash::make('secreto123'),
        ]);
        $r['registro_paso_1_usuario'] = 'ok';
        \Illuminate\Support\Facades\DB::table('perfiles')->insert(['user_id' => $u->id, 'fecha_nacimiento' => '1988-05-27']);
        $r['registro_paso_2_perfil'] = 'ok';
        $u->createToken('auth_token');
        $r['registro_paso_3_token'] = 'ok';
    } catch (\Throwable $e) {
        $r['registro_error'] = get_class($e) . ': ' . $e->getMessage() . ' en linea ' . $e->getLine();
    } finally {
        \Illuminate\Support\Facades\DB::rollBack();
    }
    return response()->json($r);
});
        

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ===== AUTH =====
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // ===== USUARIOS =====
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index']);
        Route::get('/buscar', [UsuarioController::class, 'buscar']);
        Route::get('/{id}', [UsuarioController::class, 'show']);
        Route::get('/{id}/perfil', [UsuarioController::class, 'showPerfil']);
    });

    // ===== PERFIL =====
    Route::prefix('perfil')->group(function () {
        Route::get('/me', [PerfilController::class, 'me']);
        Route::get('/{id}', [PerfilController::class, 'show']);
        Route::post('/', [PerfilController::class, 'store']);
        Route::match(["POST", "PUT"], "/perfil", [PerfilController::class, "update"]);
        Route::delete('/', [PerfilController::class, 'destroy']);
    });

    // ===== EXPERIENCIAS =====
    Route::prefix('experiencias')->group(function () {
        Route::get('/', [ExperienciaLaboralController::class, 'index']);
        Route::post('/', [ExperienciaLaboralController::class, 'store']);
        Route::put('/{id}', [ExperienciaLaboralController::class, 'update']);
        Route::delete('/{id}', [ExperienciaLaboralController::class, 'destroy']);
    });

    // ===== EDUCACION =====
    Route::prefix('educacion')->group(function () {
        Route::get('/', [EducacionController::class, 'index']);
        Route::post('/', [EducacionController::class, 'store']);
        Route::put('/{id}', [EducacionController::class, 'update']);
        Route::delete('/{id}', [EducacionController::class, 'destroy']);
    });

    // ===== HABILIDADES =====
    Route::prefix('habilidades')->group(function () {
        Route::get('/{id_perfil}', [HabilidadController::class, 'index']);
        Route::post('/', [HabilidadController::class, 'store']);
        Route::get('/{id}', [HabilidadController::class, 'show']);
        Route::put('/{id}', [HabilidadController::class, 'update']);
        Route::delete('/{id}', [HabilidadController::class, 'destroy']);
    });

    // ===== PROYECTOS =====
    Route::prefix('proyectos')->group(function () {
        Route::get('/{id_perfil}', [ProyectoController::class, 'index']);
        Route::post('/', [ProyectoController::class, 'store']);
        Route::get('/{id}', [ProyectoController::class, 'show']);
        Route::put('/{id}', [ProyectoController::class, 'update']);
        Route::delete('/{id}', [ProyectoController::class, 'destroy']);
    });

    // ===== CERTIFICACIONES =====
    Route::prefix('certificaciones')->group(function () {
        Route::get('/{id_perfil}', [CertificacionController::class, 'index']);
        Route::post('/', [CertificacionController::class, 'store']);
        Route::get('/{id}', [CertificacionController::class, 'show']);
        Route::put('/{id}', [CertificacionController::class, 'update']);
        Route::delete('/{id}', [CertificacionController::class, 'destroy']);
    });

    // ===== IDIOMAS =====
    Route::prefix('idiomas')->group(function () {
        Route::get('/{perfilId}', [IdiomaController::class, 'index']);
        Route::post('/', [IdiomaController::class, 'store']);
        Route::get('/{id}', [IdiomaController::class, 'show']);
        Route::put('/{id}', [IdiomaController::class, 'update']);
        Route::delete('/{id}', [IdiomaController::class, 'destroy']);
    });

    // ===== PERFIL IDIOMA =====


    // ===== NIVELES =====
    Route::prefix('niveles')->group(function () {
        Route::get('/', [NivelController::class, 'index']);
        Route::post('/', [NivelController::class, 'store']);
        Route::get('/{id}', [NivelController::class, 'show']);
        Route::put('/{id}', [NivelController::class, 'update']);
        Route::delete('/{id}', [NivelController::class, 'destroy']);
    });

    // ===== PODCAST =====
    Route::prefix('podcast')->group(function () {
        Route::get('/', [PodcastController::class, 'index']);
        Route::get('/podcast/tema/{tema}', [PodcastController::class, 'porTema']);
        Route::post('/', [PodcastController::class, 'store']);
        Route::get('/{id}', [PodcastController::class, 'show']);
        Route::put('/{id}', [PodcastController::class, 'update']);
        Route::delete('/{id}', [PodcastController::class, 'destroy']);
    });

    // ===== PUBLICACIONES =====
    Route::prefix('publicaciones')->group(function () {
        Route::get('/', [PublicacionController::class, 'index']);
        Route::get('/autor/{id}', [PublicacionController::class, 'porAutor']);
        Route::get('/publicaciones/tema/{tema}', [PublicacionController::class, 'porTema']);
        Route::get('/me', [PublicacionController::class, 'mePublicacion']);
        Route::post('/', [PublicacionController::class, 'store']);
        Route::get('/{id}', [PublicacionController::class, 'show']);
        Route::put('/{id}', [PublicacionController::class, 'update']);
        Route::delete('/{id}', [PublicacionController::class, 'destroy']);
    });

    // ===== COMENTARIOS =====
    Route::prefix('comentarios')->group(function () {
        Route::get('/', [ComentarioController::class, 'index']);
        Route::post('/', [ComentarioController::class, 'store']);
        Route::get('/{id}', [ComentarioController::class, 'show']);
        Route::put('/{id}', [ComentarioController::class, 'update']);
        Route::delete('/{id}', [ComentarioController::class, 'destroy']);
    });

    // ===== LIKES =====
    Route::prefix('likes')->group(function () {
        Route::post('/toggle', [LikeController::class, 'toggle']);
        Route::get('/check/{id_publicacion}', [LikeController::class, 'check']);
        Route::get('/count/{id_publicacion}', [LikeController::class, 'count']);
    });

});