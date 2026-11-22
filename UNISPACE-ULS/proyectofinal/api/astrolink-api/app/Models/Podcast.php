<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Like;
use App\Models\Comentario;

class Podcast extends Model
{
    protected $table = 'podcasts';
    protected $appends = ['contenido_url'];


    protected $fillable = [
        'tema',
        'titulo',
        'descripcion',
        'contenido',
        'user_id'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
        
        public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

        public function comentarios()
    {
        return $this->morphMany(Comentario::class, 'commentable');
    }



    public function getContenidoUrlAttribute()
    {
        if (!$this->contenido) {
            return null;
        }

        return preg_match('#^https?://#i', $this->contenido)
            ? $this->contenido
            : asset('storage/' . $this->contenido);
    }

}
