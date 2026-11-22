<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PerfilCompletoSeeder extends Seeder
{
    public function run(): void
    {
        // Tomamos los primeros 4 usuarios sin ordenar por created_at
        $users = User::take(4)->get();

        $fotos = [
            'perfiles/alexis.jfif',
            'perfiles/mau.jfif',
            'perfiles/arsenio.jpg',
            'perfiles/rodo.jpg',
        ];

        $titulos = [
            'Frontend Developer Astro',
            'Astro + Tailwind Specialist',
            'Fullstack Developer con Astro',
            'DevOps & Astro Deployments',
        ];

        $sobreMi = [
            'Especialista en Astro Framework y optimización de rendimiento web.',
            'Construyo interfaces modernas usando Astro y Tailwind CSS.',
            'Integro Astro con APIs Laravel para aplicaciones modernas.',
            'Especialista en despliegue de proyectos Astro con Docker y CI/CD.',
        ];

        foreach ($users as $index => $user) {

            DB::table('perfiles')->updateOrInsert(
          
                [
                    'titulo' => $titulos[$index] ?? 'Desarrollador Astro',
                    'foto_perfil' => $fotos[$index] ?? null,
                    'imagen_portada' => null,
                    'sobre_mi' => $sobreMi[$index] ?? null,
                    'direcccion' => 'El Salvador',
                    'telefono' => '7000-000' . ($index + 1),
                    'url_sitio_web' => null,
                    'url_linkedin' => null,
                    'url_github' => null,
                    'fecha_nacimiento' => '1999-01-01',
                     'user_id' => $user->id,
                ]
            );
        }
    }
}