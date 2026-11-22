<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Perfil;

class Idioma extends Model
{
    protected $table = 'idiomas';
    protected $fillable = [
        'perfil_id',
        'idioma',
        'nivel'
    ];

    // Relación con Perfil
        public function perfil()
    {
        return $this->belongsTo(Perfil::class);
    }
}
