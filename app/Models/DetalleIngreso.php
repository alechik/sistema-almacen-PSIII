<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleIngreso extends Model
{
    protected $table = 'detalle_ingresos';

    protected $fillable = [
        'ingreso_id',
        'cant_ingreso',
        'precio',
        'producto_id'
    ];

    public function ingreso()
    {
        return $this->belongsTo(Ingreso::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
