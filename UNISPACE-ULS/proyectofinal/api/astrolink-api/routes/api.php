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
