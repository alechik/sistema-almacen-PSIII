<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacens'; // Porque no sigue la convención plural

    protected $fillable = [
        'nombre',
        'descripcion',
        'ubicacion',
        'longitud',
        'latitud',
        'estado',
        'email',
        'cellphone',
        'user_id'
    ];

    // Relación con usuarios (creador o responsable)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación muchos a muchos con users para almacen_user
    public function users()
    {
        return $this->belongsToMany(User::class, 'almacen_user')
            ->withTimestamps();
    }

    // Relación a productos, ingresos, salidas, pedidos, etc.
    public function ingresos()
    {
        return $this->hasMany(Ingreso::class);
    }

    public function salidas()
    {
        return $this->hasMany(Salida::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
