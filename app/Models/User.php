<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
    ];


    protected $hidden = [
        'password'
    ];

    // Definir la relación con la tabla "personas"
    public function personas()
    {
        return $this->belongsTo(Personas::class, 'id_persona');
    }

    // Definir la relación con la tabla "personas"
    public function rol()
    {
        return $this->belongsTo(Roles::class, 'id_rol');
    }
}
