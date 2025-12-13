<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    const CANCELADO = 0; //el proveedor puede cancelar el pedido
    const EMITIDO = 1; //administrador de almacen
    const CONFIRMADO = 2; //el propietario
    const TERMINADO = 3; //el proveedor lo marca como terminado
    const ANULADO = 4; //el propietario puede anular el pedido
    const ENVIADO_TRAZABILIDAD = 5; //enviado a Trazabilidad, pendiente aprobación
    const APROBADO_TRAZABILIDAD = 6; //aprobado en Trazabilidad
    const RECHAZADO_TRAZABILIDAD = 7; //rechazado en Trazabilidad
    
    // Estados de Trazabilidad
    const TRAZABILIDAD_PENDIENTE = 'pendiente';
    const TRAZABILIDAD_APROBADO = 'aprobado';
    const TRAZABILIDAD_RECHAZADO = 'rechazado';
    
    protected $fillable = [
        'codigo_comprobante',
        'fecha',
        'fecha_min',
        'fecha_max',
        'estado',
        'almacen_id',
        'operador_id',
        'transportista_id',
        'proveedor_id',
        'administrador_id',
        'trazabilidad_tracking_id',
        'trazabilidad_estado',
        'enviado_a_trazabilidad',
        'fecha_envio_trazabilidad',
        'fecha_respuesta_trazabilidad'
    ];
    
    protected $casts = [
        'fecha' => 'date',
        'fecha_min' => 'date',
        'fecha_max' => 'date',
        'enviado_a_trazabilidad' => 'boolean',
        'fecha_envio_trazabilidad' => 'datetime',
        'fecha_respuesta_trazabilidad' => 'datetime',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'operador_id');
    }

    public function transportista()
    {
        return $this->belongsTo(User::class, 'transportista_id');
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }

    // public function proveedor()
    // {
    //     return $this->belongsTo(Proveedor::class, 'Proveedor_Id');
    // }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }

    public function ingreso()
    {
        return $this->hasOne(Ingreso::class, 'pedido_id');
    }
}
