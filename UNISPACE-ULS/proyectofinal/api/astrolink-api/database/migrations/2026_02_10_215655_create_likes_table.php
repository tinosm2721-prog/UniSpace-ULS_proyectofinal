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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
                // relación polimórfica
            $table->morphs('likeable');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            // evitar likes duplicados
            $table->unique(['likeable_id', 'likeable_type', 'user_id']);
            $table->index('likeable_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
