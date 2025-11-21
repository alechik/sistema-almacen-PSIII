<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'name',
        'email',
        'password',
        'company',
        'phone_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación: almacenes creados por este usuario (si es propietario)
     * Un propietario puede crear varios almacenes
     */
    public function almacenesCreados()
    {
        return $this->hasMany(Almacen::class, 'user_id');
    }

    /**
     * Relación: almacenes asignados (trabajadores)
     * Relación many-to-many → tabla almacen_user
     */
    public function almacenes()
    {
        return $this->belongsToMany(Almacen::class, 'almacen_user')
            ->withTimestamps();
    }

    /**
     * Métodos útiles para roles
     */

    public function isPropietario()
    {
        return $this->hasRole('propietario');
    }

    public function isTrabajador()
    {
        return $this->hasRole('trabajador');
    }
}
