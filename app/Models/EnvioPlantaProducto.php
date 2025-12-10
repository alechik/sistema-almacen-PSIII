<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvioPlantaProducto extends Model
{
    protected $table = 'envio_planta_productos';

    protected $fillable = [
        'envio_planta_id',
        'producto_nombre',
        'descripcion',
        'cantidad',
        'peso_unitario',
        'precio_unitario',
        'total_peso',
        'total_precio',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'peso_unitario' => 'decimal:3',
        'precio_unitario' => 'decimal:2',
        'total_peso' => 'decimal:3',
        'total_precio' => 'decimal:2',
    ];

    // Relaciones
    public function envioPlanta()
    {
        return $this->belongsTo(EnvioPlanta::class, 'envio_planta_id');
    }
}

