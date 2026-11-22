<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Perfil;

class Certificacion extends Model
{
    protected $table = 'certificaciones';
    public $timestamps = false;

    protected $fillable = [
        'certificacion',
        'organizacion',
        'fecha_emision',
        'perfil_id',
    ];

        public function perfil()
    {
        return $this->belongsTo(Perfil::class);
    }

    
}
