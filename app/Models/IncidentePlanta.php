<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentePlanta extends Model
{
    protected $table = 'incidentes_planta';

    protected $fillable = [
        'incidente_planta_id',
        'envio_planta_id',
        'tipo_incidente',
        'descripcion',
        'foto_url',
        'estado',
        'fecha_reporte',
        'fecha_resolucion',
        'notas_resolucion',
        'visto',
    ];

    protected $casts = [
        'fecha_reporte' => 'datetime',
        'fecha_resolucion' => 'datetime',
        'visto' => 'boolean',
    ];

    // Constantes
    const PRODUCTO_DANADO = 'producto_danado';
    const FALTANTE = 'faltante';
    const DEMORA = 'demora';
    const ACCIDENTE = 'accidente';
    const OTRO = 'otro';

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_RESUELTO = 'resuelto';

    // Relaciones
    public function envioPlanta()
    {
        return $this->belongsTo(EnvioPlanta::class, 'envio_planta_id');
    }

    // Helpers
    public function getTipoTextoAttribute()
    {
        return match ($this->tipo_incidente) {
            self::PRODUCTO_DANADO => 'Producto Dañado',
            self::FALTANTE => 'Producto Faltante',
            self::DEMORA => 'Demora en Entrega',
            self::ACCIDENTE => 'Accidente',
            self::OTRO => 'Otro',
            default => ucfirst($this->tipo_incidente),
        };
    }

    public function getTipoIconoAttribute()
    {
        return match ($this->tipo_incidente) {
            self::PRODUCTO_DANADO => '📦💔',
            self::FALTANTE => '❓',
            self::DEMORA => '⏰',
            self::ACCIDENTE => '🚨',
            self::OTRO => '⚠️',
            default => '❓',
        };
    }

    public function getEstadoBadgeAttribute()
    {
        return match ($this->estado) {
            self::ESTADO_PENDIENTE => '<span class="badge bg-danger">Pendiente</span>',
            self::ESTADO_EN_PROCESO => '<span class="badge bg-warning text-dark">En Proceso</span>',
            self::ESTADO_RESUELTO => '<span class="badge bg-success">Resuelto</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->estado) . '</span>',
        };
    }

    public function marcarComoVisto(): void
    {
        $this->update(['visto' => true]);
    }
}

