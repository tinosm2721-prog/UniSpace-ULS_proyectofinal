<?php
use Illuminate\Support\Facades\DB;
foreach (DB::table('users')->get() as $u) {
    DB::table('users')->where('id', $u->id)->update([
        'nombre' => trim($u->nombre),
        'apellido' => trim($u->apellido),
        'email' => trim($u->email),
    ]);
}
echo "Datos limpiados" . PHP_EOL;
