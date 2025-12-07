<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    const CANCELADO = 0; //el punto de venta puede cancelar salida
    const EMITIDO = 1; //administrador de almacen
    const CONFIRMADO = 2; //el propietario y administrador pueden confirmar la salida
    const TERMINADO = 3; //el punto de venta lo marca como terminado
    const ANULADO = 4; //el propietario y administrador puede anular la salida
    protected $table = 'salidas';

    protected $fillable = [
        'codigo_comprobante',
        'fecha',
        'fecha_min',
        'fecha_max',
        'estado',
        'almacen_id',
        'operador_id',
        'transportista_id',
        'punto_venta_id',
        'nota_venta_id',
        'tipo_salida_id',
        'vehiculo_id',
        'administrador_id',
    ];

    // Relaciones
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function transportista()
    {
        return $this->belongsTo(User::class, 'transportista_id');
    }

    public function tipoSalida()
    {
        return $this->belongsTo(TipoSalida::class, 'tipo_salida_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleSalida::class, 'salida_id');
    }
    public function administrador()
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }
}
