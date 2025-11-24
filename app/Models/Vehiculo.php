<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'placa_identificacion',
        'marca_modelo',
        'anio',
    ];

    /* ============================
        RELACIONES PARA DESCOMENTAR
       ============================ */

    // Un vehículo puede tener varios ingresos
    // public function ingresos()
    // {
    //     return $this->hasMany(Ingreso::class, 'vehiculo_id');
    // }

    // // Un vehículo puede tener varias salidas
    // public function salidas()
    // {
    //     return $this->hasMany(Salida::class, 'vehiculo_id');
    // }
}
