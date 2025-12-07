<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleSalida extends Model
{
    protected $table = 'detalle_salidas';

    protected $fillable = [
        'salida_id',
        'cant_salida',
        'precio',
        'producto_id',
    ];

    public function salida()
    {
        return $this->belongsTo(Salida::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
