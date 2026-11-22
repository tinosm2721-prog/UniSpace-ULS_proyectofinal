<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PublicacionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::take(4)->get();

        if ($users->count() < 4) {
            echo "Necesitas al menos 4 usuarios creados.\n";
            return;
        }

        DB::table('publicaciones')->insert([
            [   
                
                'tema' => 'Informática',
                'titulo' => 'Introducción a Astro Framework',
                'contenido' => 'Astro es un framework moderno enfocado en rendimiento. Permite construir sitios estáticos ultra rápidos y usar React, Vue o Svelte dentro del mismo proyecto.',
                'imagen' => null,
                'publica' => true,
                'user_id' => $users[0]->id,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]
        ]);
    }
}