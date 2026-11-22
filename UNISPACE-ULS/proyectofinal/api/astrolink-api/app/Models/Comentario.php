<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Publicacion;
use App\Models\User;

class Comentario extends Model
{
    protected $table = 'comentarios';

    public $timestamps = false;

    protected $fillable = [
        'comentario',
        'user_id'
    ];


        public function commentable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }



}
