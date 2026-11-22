<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {

            $usuarios = [
                [
                    'nombre' => 'AleSantos Alexis ',
                    'apellido' => 'Morataya Ruiz',
                    'email' => 'MoratayaRuiz@example.com',
                    'password' => '12345678',
                    'fecha_nacimiento' => '2000-05-10',
                ],
                [
                    'nombre' => 'Carlos Mauricio',
                    'apellido' => ' Guerrero Granada',
                    'email' => ' GuerreroGranada@example.com',
                    'password' => '12345678',
                    'fecha_nacimiento' => '1998-08-22',
                ],
                [
                    'nombre' => 'Miguel Arsenio',
                    'apellido' => 'Alas Crespin',
                    'email' => 'AlasCrespin@example.com',
                    'password' => '12345678',
                    'fecha_nacimiento' => '1997-01-15',
                ],
                [
                    'nombre' => 'José Rodolfo ',
                    'apellido' => 'Vigil Martínez',
                    'email' => 'VigilMartínez@example.com',
                    'password' => '12345678',
                    'fecha_nacimiento' => '1999-01-15',
                ],
            ];

            foreach ($usuarios as $data) {

                $user = User::create([
                    'nombre'       => $data['nombre'],
                    'apellido'     => $data['apellido'],
                    'email'        => $data['email'],
                    'password'     => Hash::make($data['password']),
                    'esta_activo'  => true,
                    'ultimo_login' => null
                ]);

                DB::table('perfiles')->insert([
                    'user_id' => $user->id,
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}