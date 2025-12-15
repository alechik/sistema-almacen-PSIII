@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalles del Incidente</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('incidentes.index') }}">Incidentes</a></li>
                        <li class="breadcrumb-item active">Detalles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Incidente #{{ $incidente->id }}
                            </h5>
                            <a href="{{ route('incidentes.index') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Información del Incidente -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-danger"><i class="bi bi-exclamation-triangle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tipo de Incidente</span>
                                            <span class="info-box-number">{{ $incidente->tipo_incidente }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-warning"><i class="bi bi-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fecha de Reporte</span>
                                            <span class="info-box-number">{{ $incidente->fecha_reporte->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon {{ $incidente->accion === 'cancelar' ? 'bg-danger' : 'bg-info' }}">
                                            <i class="bi bi-{{ $incidente->accion === 'cancelar' ? 'x-circle' : 'arrow-right-circle' }}"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Acción Tomada</span>
                                            <span class="info-box-number">
                                                @if($incidente->accion === 'cancelar')
                                                    Cancelado
                                                @else
                                                    Continúa
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon 
                                            @if($incidente->estado === 'pendiente') bg-warning
                                            @elseif($incidente->estado === 'en_proceso') bg-primary
                                            @else bg-success
                                            @endif">
                                            <i class="bi bi-flag"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Estado</span>
                                            <span class="info-box-number">
                                                @if($incidente->estado === 'pendiente')
                                                    Pendiente
                                                @elseif($incidente->estado === 'en_proceso')
                                                    En Proceso
                                                @else
                                                    Resuelto
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Pedido y Envío -->
                            <div class="card card-outline card-primary mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-box-seam me-1"></i>
                                        Información del Pedido y Envío
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Código de Pedido:</strong><br>
                                            <code>{{ $incidente->pedido->codigo_comprobante ?? 'N/A' }}</code>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Código de Envío:</strong><br>
                                            <code>{{ $incidente->envio_codigo }}</code>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Transportista:</strong><br>
                                            {{ $incidente->transportista_nombre ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción del Incidente -->
                            <div class="card card-outline card-danger mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-file-text me-1"></i>
                                        Descripción del Incidente
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $incidente->descripcion }}</p>
                                </div>
                            </div>

                            <!-- Foto del Incidente -->
                            @if($incidente->foto_url)
                            <div class="card card-outline card-info mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-camera me-1"></i>
                                        Foto del Incidente
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <img src="{{ route('incidentes.foto', $incidente) }}" 
                                         alt="Foto del incidente" 
                                         class="img-fluid rounded shadow"
                                         style="max-height: 500px;">
                                </div>
                            </div>
                            @endif

                            <!-- Ubicación -->
                            @if($incidente->ubicacion_lat && $incidente->ubicacion_lng)
                            <div class="card card-outline card-success mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-geo-alt me-1"></i>
                                        Ubicación del Incidente
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">
                                        <strong>Coordenadas:</strong><br>
                                        Latitud: {{ $incidente->ubicacion_lat }}<br>
                                        Longitud: {{ $incidente->ubicacion_lng }}
                                    </p>
                                </div>
                            </div>
                            @endif

                            <!-- Notas de Resolución -->
                            @if($incidente->notas_resolucion)
                            <div class="card card-outline card-success mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Notas de Resolución
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $incidente->notas_resolucion }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

