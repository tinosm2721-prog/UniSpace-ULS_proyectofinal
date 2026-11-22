<?php
foreach (Illuminate\Support\Facades\DB::table('users')->get() as $u) {
    echo $u->id . ' | ' . $u->nombre . ' ' . $u->apellido . ' | ' . $u->email . PHP_EOL;
}
