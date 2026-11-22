<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
echo "Columnas de perfiles: " . implode(', ', Schema::getColumnListing('perfiles')) . PHP_EOL;
foreach (DB::table('perfiles')->get() as $p) {
    $a = (array) $p;
    foreach ($a as $k => $v) { if (is_string($v) && strlen($v) > 40) $a[$k] = substr($v, 0, 40) . '...'; }
    echo json_encode($a, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
