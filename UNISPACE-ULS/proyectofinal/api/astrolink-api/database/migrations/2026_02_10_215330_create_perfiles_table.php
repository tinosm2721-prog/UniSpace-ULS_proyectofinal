<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('perfiles', function (Blueprint $table) {
    $table->id();
    $table->string('titulo')->nullable();
    $table->string('foto_perfil')->nullable();
    $table->string('imagen_portada')->nullable();
    $table->text('sobre_mi')->nullable();
    $table->text('direccion')->nullable();
    $table->string('telefono')->unique()->nullable();
    $table->string('url_sitio_web')->nullable();
    $table->string('url_linkedin')->nullable();
    $table->string('url_github')->nullable();
    $table->date('fecha_nacimiento');
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();

   
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles');
    }
};
