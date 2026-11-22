<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Perfil;

class Proyecto extends Model
{
    protected $table = 'proyectos';

    protected $fillable = [

        'proyecto',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'url_proyecto',
        'perfil_id'
    ];


        public function perfil()
    {
        return $this->belongsTo(Perfil::class);
    }
}
