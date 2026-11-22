<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjetoPerdido extends Model
{
    protected $table = 'objetos_perdidos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'tipo',
        'lugar_id',
        'estado',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
