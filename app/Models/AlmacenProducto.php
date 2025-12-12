<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlmacenProducto extends Model
{
    protected $table = 'almacen_productos';   // No cambiar, debe coincidir con tu BD

    protected $primaryKey = 'id';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'stock',
        'stock_minimo',
        'en_pedido',
    ];

    // RELACIONES ------------------------------------------------------

    // Un registro de almacén-producto pertenece a un producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }

    // Un registro de almacén-producto pertenece a un almacén
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id', 'id');
    }
}
