<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Perfil; 

class ExperienciaLaboral extends Model
{
    protected $table = 'experiencias_laborales';
    public $timestamps =false;
    protected $fillable = [
        'nombre_empresa',
        'descripcion_puesto',
        'direccion',
        'fecha_inicio',
        'fecha_fin',
        'perfil_id'
    ];



    public function perfil()
    {
        return $this->belongsTo(Perfil::class);
    }
}
