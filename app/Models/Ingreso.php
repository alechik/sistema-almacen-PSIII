<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingreso extends Model
{
    protected $table = 'ingresos';

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
        'pedido_id',
        'tipo_ingreso_id',
        'vehiculo_id',
        'administrador_id'
    ];

    // Relación: un ingreso pertenece a un almacén
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

    // public function proveedor()
    // {
    //     return $this->belongsTo(Proveedor::class);
    // }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function tipoIngreso()
    {
        return $this->belongsTo(TipoIngreso::class, 'tipo_ingreso_id');
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }

    // Relación con detalle_ingreso
    public function detalles()
    {
        return $this->hasMany(DetalleIngreso::class);
    }
}
