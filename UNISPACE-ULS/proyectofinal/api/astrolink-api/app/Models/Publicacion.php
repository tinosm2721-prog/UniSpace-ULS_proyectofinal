<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use  App\Models\User;

class Publicacion extends Model
{
    protected $table = 'publicaciones';

protected $fillable = [
    'tema',
    'titulo',
    'contenido',
    'imagen',
    'publica',
    'user_id',
    'ubicacion',
    'aula',
    'lugar_id',
    'lat',
    'lng'
];



    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function likes()
    {
    return $this->morphMany(Like::class,'likeable');
    }

    public function comentarios()
    {
    return $this->morphMany(Comentario::class, 'commentable');
    }

     public function getImagenAttribute($value)
    {
        if (!$value) {
            return null;
        }

        return asset('storage/' . $value);
    }

}
