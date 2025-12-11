<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlmacenUser extends Model
{
    use HasFactory;

    protected $table = 'almacen_users'; // Nombre de tabla creado por el comando

    protected $fillable = [
        'user_id',
        'almacen_id',
    ];

    /**
     * Relación: un registro pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: un registro pertenece a un almacén
     */
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
}
