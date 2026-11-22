<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Perfil; 

class Educacion extends Model
{
    public $timestamps = false; // 🔥 IMPORTANTE
    protected $table = 'educacion';

    protected $fillable = [
        'nombre_institucion',
        'titulo',
        'fecha_inicio',
        'fecha_fin',
        'perfil_id'
    ];

    public function perfil()
    {
        return $this->belongsTo(Perfil::class);
    }
}

