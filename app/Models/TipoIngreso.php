<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoIngreso extends Model
{
    use HasFactory;

    protected $table = 'tipo_ingresos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // DESCOMENTAR PARA TRABAJAR YA CON LOS INGRESOS
    // public function ingresos()
    // {
    //     return $this->hasMany(Ingreso::class, 'tipo_ingreso_id');
    // }
}
