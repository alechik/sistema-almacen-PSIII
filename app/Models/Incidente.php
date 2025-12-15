<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidente extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'envio_id',
        'envio_codigo',
        'incidente_id_planta',
        'transportista_id',
        'transportista_nombre',
        'tipo_incidente',
        'descripcion',
        'foto_url',
        'accion',
        'estado',
        'ubicacion_lat',
        'ubicacion_lng',
        'fecha_reporte',
        'notas_resolucion',
    ];

    protected $casts = [
        'fecha_reporte' => 'datetime',
        'ubicacion_lat' => 'decimal:8',
        'ubicacion_lng' => 'decimal:8',
    ];

    // Relaciones
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
