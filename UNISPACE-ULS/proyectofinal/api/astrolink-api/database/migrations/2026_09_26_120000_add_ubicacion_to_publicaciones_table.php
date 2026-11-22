<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->string('ubicacion')->nullable()->after('imagen');
            $table->string('aula')->nullable()->after('ubicacion');
            $table->string('lugar_id')->nullable()->after('aula');
            $table->decimal('lat', 10, 7)->nullable()->after('lugar_id');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('publicaciones', function (Blueprint $table) {
            $table->dropColumn(['ubicacion', 'aula', 'lugar_id', 'lat', 'lng']);
        });
    }
};
