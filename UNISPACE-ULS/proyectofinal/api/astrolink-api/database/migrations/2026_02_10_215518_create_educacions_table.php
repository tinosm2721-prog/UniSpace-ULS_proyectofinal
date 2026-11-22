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
Schema::create('educacion', function (Blueprint $table) {
    $table->id();
    $table->string('nombre_institucion', 200);
    $table->string('titulo');;
    $table->date('fecha_inicio');
    $table->date('fecha_fin')->nullable();
    $table->timestamp('creado_en')->useCurrent();
    $table->foreignId('perfil_id')->constrained('perfiles')->cascadeOnDelete();
    
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educacion');
    }
};
