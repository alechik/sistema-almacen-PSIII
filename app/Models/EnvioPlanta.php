<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvioPlanta extends Model
{
    protected $table = 'envios_planta';

    protected $fillable = [
        'envio_planta_id',
        'codigo',
        'almacen_id',
        'solicitante_id',
        'estado',
        'fecha_creacion',
        'fecha_estimada_entrega',
        'hora_estimada',
        'fecha_asignacion',
        'fecha_inicio_transito',
        'fecha_entrega',
        'transportista_nombre',
        'transportista_telefono',
        'vehiculo_placa',
        'vehiculo_descripcion',
        'ubicacion_lat',
        'ubicacion_lng',
        'ubicacion_actualizada_at',
        'origen_lat',
        'origen_lng',
        'origen_direccion',
        'destino_lat',
        'destino_lng',
        'destino_direccion',
        'total_cantidad',
        'total_peso',
        'total_precio',
        'observaciones',
        'firma_transportista',
        'fecha_rechazo',
        'motivo_rechazo',
        'visto',
        'sincronizado_at',
    ];

    protected $casts = [
        'fecha_creacion' => 'date',
        'fecha_estimada_entrega' => 'date',
        'fecha_asignacion' => 'datetime',
        'fecha_inicio_transito' => 'datetime',
        'fecha_entrega' => 'datetime',
        'fecha_rechazo' => 'datetime',
        'ubicacion_actualizada_at' => 'datetime',
        'sincronizado_at' => 'datetime',
        'ubicacion_lat' => 'decimal:7',
        'ubicacion_lng' => 'decimal:7',
        'origen_lat' => 'decimal:7',
        'origen_lng' => 'decimal:7',
        'destino_lat' => 'decimal:7',
        'destino_lng' => 'decimal:7',
        'total_peso' => 'decimal:3',
        'total_precio' => 'decimal:2',
        'visto' => 'boolean',
    ];

    // Constantes de estado
    const PENDIENTE = 'pendiente';
    const ASIGNADO = 'asignado';
    const ACEPTADO = 'aceptado';
    const EN_TRANSITO = 'en_transito';
    const ENTREGADO = 'entregado';
    const CANCELADO = 'cancelado';
    const RECHAZADO = 'rechazado';

    // Relaciones
    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function productos()
    {
        return $this->hasMany(EnvioPlantaProducto::class, 'envio_planta_id');
    }

    public function incidentes()
    {
        return $this->hasMany(IncidentePlanta::class, 'envio_planta_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', self::PENDIENTE);
    }

    public function scopeEnTransito($query)
    {
        return $query->where('estado', self::EN_TRANSITO);
    }

    public function scopeEntregados($query)
    {
        return $query->where('estado', self::ENTREGADO);
    }

    public function scopeActivos($query)
    {
        return $query->whereNotIn('estado', [self::ENTREGADO, self::CANCELADO]);
    }

    public function scopeParaAlmacen($query, $almacenId)
    {
        return $query->where('almacen_id', $almacenId);
    }

    public function scopeNoVistos($query)
    {
        return $query->where('visto', false);
    }

    public function scopeDelSolicitante($query, $userId)
    {
        return $query->where('solicitante_id', $userId);
    }

    // Helpers
    public function getEstadoBadgeAttribute()
    {
        return match ($this->estado) {
            self::PENDIENTE => '<span class="badge bg-secondary">Pendiente</span>',
            self::ASIGNADO => '<span class="badge bg-info">Asignado</span>',
            self::ACEPTADO => '<span class="badge bg-primary">Aceptado</span>',
            self::EN_TRANSITO => '<span class="badge bg-warning text-dark">En Tránsito</span>',
            self::ENTREGADO => '<span class="badge bg-success">Entregado</span>',
            self::CANCELADO => '<span class="badge bg-danger">Cancelado</span>',
            self::RECHAZADO => '<span class="badge bg-danger">Rechazado</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->estado) . '</span>',
        };
    }

    public function getEstadoIconoAttribute()
    {
        return match ($this->estado) {
            self::PENDIENTE => '⏳',
            self::ASIGNADO => '📋',
            self::ACEPTADO => '✅',
            self::EN_TRANSITO => '🚚',
            self::ENTREGADO => '📦',
            self::CANCELADO => '❌',
            self::RECHAZADO => '🚫',
            default => '❓',
        };
    }

    public function estaEnTransito(): bool
    {
        return $this->estado === self::EN_TRANSITO;
    }

    public function estaEntregado(): bool
    {
        return $this->estado === self::ENTREGADO;
    }

    public function tieneIncidentes(): bool
    {
        return $this->incidentes()->where('estado', '!=', 'resuelto')->exists();
    }

    public function tieneIncidentesPendientes(): bool
    {
        return $this->incidentes()->where('estado', 'pendiente')->exists();
    }

    public function marcarComoVisto(): void
    {
        $this->update(['visto' => true]);
    }
}

