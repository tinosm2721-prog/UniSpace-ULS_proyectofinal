<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
echo "Columnas: " . implode(', ', Schema::getColumnListing('users')) . PHP_EOL;
foreach ([6, 3, 5] as $id) {
    $u = (array) DB::table('users')->where('id', $id)->first();
    unset($u['password'], $u['remember_token']);
    echo json_encode($u, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
$tablas = array_map(fn($t) => $t->name, DB::select("select name from sqlite_master where type='table'"));
echo "Tablas: " . implode(', ', $tablas) . PHP_EOL;
