<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoSalida extends Model
{
    // Si Laravel generó la tabla tipo_salidas, NO cambiamos ese nombre
    protected $table = 'tipo_salidas';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /**
     * LUEGO DESCOMENTAR SI ES NECESARIO
     * Relación: Un Tipo de Salida tiene muchas salidas
     */
    // public function salidas()
    // {
    //     return $this->hasMany(Salida::class, 'tipo_salida_id');
    // }
}
