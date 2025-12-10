@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-box-seam text-primary"></i> 
                        Envío: {{ $envioPlanta->codigo }}
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('envios-planta.index') }}">Envíos Planta</a></li>
                        <li class="breadcrumb-item active">{{ $envioPlanta->codigo }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Estado de Sincronización -->
            @if($envioPlanta->envio_planta_id)
                <div class="alert alert-info d-flex align-items-center mb-4">
                    <i class="bi bi-cloud-check fs-4 me-2"></i>
                    <div class="flex-grow-1">
                        <strong>Sincronizado con Planta</strong>
                        <br>
                        <small>ID en Planta: #{{ $envioPlanta->envio_planta_id }} | 
                        Última sincronización: {{ $envioPlanta->sincronizado_at?->format('d/m/Y H:i') ?? 'Nunca' }}</small>
                    </div>
                    @if($datosActualizados)
                        <span class="badge bg-success"><i class="bi bi-check"></i> Datos actualizados</span>
                    @endif
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center mb-4">
                    <i class="bi bi-cloud-slash fs-4 me-2"></i>
                    <div>
                        <strong>Pendiente de sincronización</strong>
                        <br>
                        <small>Este pedido aún no ha sido procesado por Planta</small>
                    </div>
                </div>
            @endif

            <div class="row">
                <!-- Información Principal -->
                <div class="col-lg-8">
                    
                    <!-- Estado y Timeline -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle"></i> Estado del Envío
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <span class="fs-4">{!! $envioPlanta->estado_badge !!}</span>
                                    <span class="ms-2 fs-3">{{ $envioPlanta->estado_icono }}</span>
                                </div>
                                <div class="text-end">
                                    @if($envioPlanta->estaEnTransito())
                                        <a href="{{ route('envios-planta.monitoreo', $envioPlanta) }}" 
                                           class="btn btn-warning">
                                            <i class="bi bi-geo-alt"></i> Ver en Mapa
                                        </a>
                                    @endif
                                    @if($envioPlanta->estaEntregado())
                                        <a href="{{ route('envios-planta.nota-recepcion', $envioPlanta) }}" 
                                           class="btn btn-success">
                                            <i class="bi bi-file-pdf"></i> Nota de Recepción
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="timeline-steps">
                                <div class="timeline-step {{ $envioPlanta->fecha_creacion ? 'active' : '' }}">
                                    <div class="timeline-content">
                                        <div class="inner-circle"></div>
                                        <p class="mb-0"><strong>Creado</strong></p>
                                        <small>{{ $envioPlanta->fecha_creacion?->format('d/m/Y') ?? '-' }}</small>
                                    </div>
                                </div>
                                <div class="timeline-step {{ $envioPlanta->fecha_asignacion ? 'active' : '' }}">
                                    <div class="timeline-content">
                                        <div class="inner-circle"></div>
                                        <p class="mb-0"><strong>Asignado</strong></p>
                                        <small>{{ $envioPlanta->fecha_asignacion?->format('d/m/Y H:i') ?? '-' }}</small>
                                    </div>
                                </div>
                                <div class="timeline-step {{ $envioPlanta->fecha_inicio_transito ? 'active' : '' }}">
                                    <div class="timeline-content">
                                        <div class="inner-circle"></div>
                                        <p class="mb-0"><strong>En Tránsito</strong></p>
                                        <small>{{ $envioPlanta->fecha_inicio_transito?->format('d/m/Y H:i') ?? '-' }}</small>
                                    </div>
                                </div>
                                <div class="timeline-step {{ $envioPlanta->fecha_entrega ? 'active' : '' }}">
                                    <div class="timeline-content">
                                        <div class="inner-circle"></div>
                                        <p class="mb-0"><strong>Entregado</strong></p>
                                        <small>{{ $envioPlanta->fecha_entrega?->format('d/m/Y H:i') ?? '-' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-box"></i> Productos del Envío
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Peso Unit.</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($envioPlanta->productos as $producto)
                                        <tr>
                                            <td>{{ $producto->producto_nombre }}</td>
                                            <td class="text-center">{{ $producto->cantidad }}</td>
                                            <td class="text-end">{{ number_format($producto->peso_unitario, 2) }} kg</td>
                                            <td class="text-end">Bs. {{ number_format($producto->precio_unitario, 2) }}</td>
                                            <td class="text-end">Bs. {{ number_format($producto->total_precio, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3">
                                                No hay productos registrados
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="2">TOTALES</th>
                                        <th class="text-center">{{ $envioPlanta->total_cantidad }} unid.</th>
                                        <th class="text-end">{{ number_format($envioPlanta->total_peso, 2) }} kg</th>
                                        <th class="text-end">Bs. {{ number_format($envioPlanta->total_precio, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Incidentes -->
                    @if($envioPlanta->incidentes->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Incidentes Reportados ({{ $envioPlanta->incidentes->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach($envioPlanta->incidentes as $incidente)
                                <div class="alert {{ $incidente->estado == 'resuelto' ? 'alert-success' : 'alert-warning' }} mb-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $incidente->tipo_icono }} {{ $incidente->tipo_texto }}</strong>
                                            {!! $incidente->estado_badge !!}
                                        </div>
                                        <small>{{ $incidente->fecha_reporte?->format('d/m/Y H:i') }}</small>
                                    </div>
                                    <p class="mb-0 mt-2">{{ $incidente->descripcion }}</p>
                                    @if($incidente->foto_url)
                                        <a href="{{ $incidente->foto_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bi bi-image"></i> Ver Foto
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    
                    <!-- Info del Almacén -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="bi bi-building"></i> Almacén Destino</h6>
                        </div>
                        <div class="card-body">
                            <h5>{{ $envioPlanta->almacen->nombre ?? 'No asignado' }}</h5>
                            <p class="text-muted mb-1">
                                <i class="bi bi-geo-alt"></i> {{ $envioPlanta->destino_direccion ?? 'Sin dirección' }}
                            </p>
                        </div>
                    </div>

                    <!-- Info del Transportista -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bi bi-person-badge"></i> Transportista</h6>
                        </div>
                        <div class="card-body">
                            @if($envioPlanta->transportista_nombre)
                                <h5>{{ $envioPlanta->transportista_nombre }}</h5>
                                @if($envioPlanta->transportista_telefono)
                                    <p class="mb-1">
                                        <i class="bi bi-telephone"></i> {{ $envioPlanta->transportista_telefono }}
                                    </p>
                                @endif
                            @else
                                <p class="text-muted mb-0">Sin asignar</p>
                            @endif
                        </div>
                    </div>

                    <!-- Info del Vehículo -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="bi bi-truck"></i> Vehículo</h6>
                        </div>
                        <div class="card-body">
                            @if($envioPlanta->vehiculo_placa)
                                <h5><i class="bi bi-upc"></i> {{ $envioPlanta->vehiculo_placa }}</h5>
                                @if($envioPlanta->vehiculo_descripcion)
                                    <p class="text-muted mb-0">{{ $envioPlanta->vehiculo_descripcion }}</p>
                                @endif
                            @else
                                <p class="text-muted mb-0">Sin asignar</p>
                            @endif
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-calendar"></i> Fechas</h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Fecha Estimada:</span>
                                <strong>{{ $envioPlanta->fecha_estimada_entrega?->format('d/m/Y') ?? '-' }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Hora Estimada:</span>
                                <strong>{{ $envioPlanta->hora_estimada ?? '-' }}</strong>
                            </li>
                            @if($envioPlanta->fecha_entrega)
                            <li class="list-group-item d-flex justify-content-between bg-success text-white">
                                <span>Entregado:</span>
                                <strong>{{ $envioPlanta->fecha_entrega->format('d/m/Y H:i') }}</strong>
                            </li>
                            @endif
                        </ul>
                    </div>

                    <!-- Observaciones -->
                    @if($envioPlanta->observaciones)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-chat-text"></i> Observaciones</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $envioPlanta->observaciones }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Acciones -->
                    <div class="card">
                        <div class="card-body d-grid gap-2">
                            <a href="{{ route('envios-planta.documento', $envioPlanta) }}" 
                               class="btn btn-outline-primary" target="_blank">
                                <i class="bi bi-file-text"></i> Ver Documento Planta
                            </a>
                            <a href="{{ route('envios-planta.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al Listado
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</main>

<style>
.timeline-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
}
.timeline-steps::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 0;
    right: 0;
    height: 4px;
    background: #e9ecef;
    z-index: 1;
}
.timeline-step {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
}
.timeline-step .inner-circle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #dee2e6;
    margin: 0 auto 10px;
    border: 4px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}
.timeline-step.active .inner-circle {
    background: #28a745;
    box-shadow: 0 0 0 2px #28a745;
}
.timeline-step.active ~ .timeline-step .inner-circle {
    background: #dee2e6;
    box-shadow: 0 0 0 2px #dee2e6;
}
</style>

@endsection

