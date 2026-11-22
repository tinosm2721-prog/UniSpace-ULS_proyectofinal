<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Perfil;
use App\Models\Like;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';


    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'password',
        'esta_activo',
        'ultimo_login'

    ];

    /*
    |--------------------------------------------------------------------------
    | Campos ocultos
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'esta_activo' => 'boolean',
        'ultimo_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTH (Eloquent solo aquí)
    |--------------------------------------------------------------------------
    */

    public static function findByEmail(string $email)
    {
        return self::where('email', $email)
            ->where('esta_activo', true)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | CACHE READY (para alto tráfico)
    |--------------------------------------------------------------------------
    */

    public static function cachedById(int $id)
    {
        return Cache::remember("user:$id", 300, function () use ($id) {
            return DB::table('users')
                ->select('id', 'nombre', 'apellido', 'email', 'esta_activo')
                ->where('id', $id)
                ->first();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar último login
    |--------------------------------------------------------------------------
    */

    public function updateLastLogin()
    {
        $this->update([
            'ultimo_login' => now()
        ]);
    }
    public function perfil()
{
    return $this->hasOne(Perfil::class);
}

public function likes(){
    return $this->hasMany(Like::class);
}

}
