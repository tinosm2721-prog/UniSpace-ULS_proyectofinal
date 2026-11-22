<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Perfil; 

class Habilidad extends Model
{
    public $timestamps = false;
    protected $table = 'habilidades';
    protected $fillable = [
       'habilidad',
       'perfil_id'
    ];

        public function perfil()
    {
        return $this->belongsTo(Perfil::class);
    }
}
