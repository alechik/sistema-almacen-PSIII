@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-exclamation-triangle text-danger"></i> 
                        Incidente #{{ $incidente->id }}
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('envios-planta.incidentes') }}">Incidentes</a></li>
                        <li class="breadcrumb-item active">#{{ $incidente->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-8">
                    
                    <!-- Info del Incidente -->
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                {{ $incidente->tipo_icono }} Detalles del Incidente
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Tipo de Incidente</label>
                                    <h5>{{ $incidente->tipo_icono }} {{ $incidente->tipo_texto }}</h5>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Estado</label>
                                    <div>{!! $incidente->estado_badge !!}</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted">Descripción</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $incidente->descripcion ?? 'Sin descripción proporcionada' }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Fecha de Reporte</label>
                                    <p class="mb-0">
                                        <i class="bi bi-calendar"></i>
                                        {{ $incidente->fecha_reporte?->format('d/m/Y H:i:s') ?? '-' }}
                                    </p>
                                </div>
                                @if($incidente->fecha_resolucion)
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Fecha de Resolución</label>
                                    <p class="mb-0 text-success">
                                        <i class="bi bi-check-circle"></i>
                                        {{ $incidente->fecha_resolucion->format('d/m/Y H:i:s') }}
                                    </p>
                                </div>
                                @endif
                            </div>

                            @if($incidente->notas_resolucion)
                            <div class="mb-3">
                                <label class="form-label text-muted">Notas de Resolución</label>
                                <div class="p-3 bg-success text-white rounded">
                                    {!! nl2br(e($incidente->notas_resolucion)) !!}
                                </div>
                            </div>
                            @endif

                            @if($incidente->foto_url)
                            <div class="mb-3">
                                <label class="form-label text-muted">Evidencia Fotográfica</label>
                                <div>
                                    <a href="{{ $incidente->foto_url }}" target="_blank" class="btn btn-primary">
                                        <i class="bi bi-image"></i> Ver Fotografía
                                    </a>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Productos del Envío -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-box"></i> Productos Afectados (Envío)
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incidente->envioPlanta->productos as $producto)
                                    <tr>
                                        <td>{{ $producto->producto_nombre }}</td>
                                        <td class="text-center">{{ $producto->cantidad }}</td>
                                        <td class="text-end">Bs. {{ number_format($producto->total_precio, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4">
                    
                    <!-- Info del Envío -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-box-seam"></i> Envío Relacionado
                            </h6>
                        </div>
                        <div class="card-body">
                            <h5>{{ $incidente->envioPlanta->codigo }}</h5>
                            <p class="mb-1">
                                <i class="bi bi-building"></i> 
                                {{ $incidente->envioPlanta->almacen->nombre ?? 'N/A' }}
                            </p>
                            <p class="mb-2">
                                {!! $incidente->envioPlanta->estado_badge !!}
                            </p>
                            <a href="{{ route('envios-planta.show', $incidente->envioPlanta) }}" 
                               class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-eye"></i> Ver Envío Completo
                            </a>
                        </div>
                    </div>

                    <!-- Impacto -->
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="bi bi-info-circle"></i> Impacto
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($incidente->estado == 'pendiente')
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Atención:</strong> Este incidente aún no ha sido atendido por Planta.
                                    Es posible que el envío sea cancelado o reprogramado.
                                </div>
                            @elseif($incidente->estado == 'en_proceso')
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-hourglass-split"></i>
                                    <strong>En proceso:</strong> Planta está trabajando en resolver este incidente.
                                </div>
                            @else
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle"></i>
                                    <strong>Resuelto:</strong> Este incidente ha sido atendido y resuelto.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('envios-planta.incidentes') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver a Incidentes
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</main>

@endsection

