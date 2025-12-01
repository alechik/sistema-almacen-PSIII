<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    const CANCELADO = 0;
    const EMITIDO = 1;
    const CONFIRMADO = 2;
    const TERMINADO = 3;
    const ANULADO = 4;
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
        'administrador_id'
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
}
