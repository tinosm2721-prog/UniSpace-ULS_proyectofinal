<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\User; 
use App\Models\Educacion; 
use App\Models\Proyecto; 
use App\Models\Certificacion; 
use App\Models\ExperienciaLaboral; 
use App\Models\Habilidad; 
use App\Models\Idioma; 


class Perfil extends Model
{
    protected $table = 'perfiles';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'foto_perfil',
        'imagen_portada',
        'sobre_mi',
        'direccion',
        'telefono',
        'url_sitio_web',
        'url_linkedin',
        'url_github',
        'fecha_nacimiento',
        'user_id',
    ];

    /*
    |-----------------------------------------
    | Subir imagen de perfil
    |-----------------------------------------
    */
    public function uploadImage($image)
    {
        if (!$image) {
            return;
        }

        // Eliminar imagen anterior si existe
        if ($this->foto_perfil && Storage::disk('public')->exists($this->foto_perfil)) {
            Storage::disk('public')->delete($this->foto_perfil);
        }

        // Generar nombre único
        $imageName = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

        // Guardar en storage/app/public/perfiles
        $path = $image->storeAs('perfiles', $imageName, 'public');

        // Guardar ruta en BD
        $this->foto_perfil = $path;
        $this->save();
    }

    /*
    |-----------------------------------------
    | Obtener URL pública de imagen
    |-----------------------------------------
    */
    public function getImageUrl()
    {
        return $this->foto_perfil
            ? asset('storage/' . $this->foto_perfil)
            : null;
    }

//funciones de las relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function idioma()
    {
    return $this->hasMany(Idioma::class);
    }

    public function educacion()
    {
    return $this->hasMany(Educacion::class);
    }

    public function certificacion()
    {
    return $this->hasMany(Certificacion::class);
    }

    public function experienciaLaboral()
    {
    return $this->hasMany(ExperienciaLaboral::class);
    }

    public function habilidad()
    {
    return $this->hasMany(Habilidad::class);
    }

    public function proyecto()
    {
    return $this->hasMany(Proyecto::class);
    }


}
