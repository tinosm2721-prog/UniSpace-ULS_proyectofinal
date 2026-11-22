<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\User; 
use App\Models\Publicacion; 


class Like extends Model
{
    protected $table = 'likes';

    public $timestamps = false;

    protected $fillable = [
            'user_id'
    ];

    public function likeable()
    {
        return $this->morphTo();
    }

            public function user()
    {
        return $this->belongsTo(User::class);
    }
}
