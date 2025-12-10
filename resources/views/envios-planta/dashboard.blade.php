@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-speedometer2 text-primary"></i> Dashboard de Envíos
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Dashboard Envíos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Estado de Conexión -->
            <div class="alert {{ $plantaConectada ? 'alert-success' : 'alert-danger' }} d-flex align-items-center mb-4">
                <i class="bi {{ $plantaConectada ? 'bi-check-circle' : 'bi-x-circle' }} fs-4 me-2"></i>
                <div>
                    <strong>Estado de Conexión con Planta:</strong>
                    {{ $plantaConectada ? 'Conectado correctamente' : 'Sin conexión - Verifique la configuración' }}
                </div>
                <form action="{{ route('envios-planta.sincronizar') }}" method="POST" class="ms-auto">
                    @csrf
                    <button type="submit" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-repeat"></i> Sincronizar Ahora
                    </button>
                </form>
            </div>

            <div class="row">
                <!-- Envíos en Tránsito -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="bi bi-truck"></i> 
                                Envíos en Tránsito ({{ $enviosEnTransito->count() }})
                            </h5>
                        </div>
                        <div class="card-body">
                            @forelse($enviosEnTransito as $envio)
                                <div class="card mb-3 border-warning">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="mb-1">
                                                    🚚 {{ $envio->codigo }}
                                                </h5>
                                                <p class="mb-1 text-muted">
                                                    <i class="bi bi-building"></i> {{ $envio->almacen->nombre ?? 'N/A' }}
                                                </p>
                                                <p class="mb-0">
                                                    <i class="bi bi-person"></i> {{ $envio->transportista_nombre ?? 'Sin asignar' }}
                                                    <span class="mx-2">|</span>
                                                    <i class="bi bi-truck-front"></i> {{ $envio->vehiculo_placa ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-warning text-dark mb-2">En Tránsito</span>
                                                <br>
                                                <a href="{{ route('envios-planta.monitoreo', $envio) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="bi bi-geo-alt"></i> Ver en Mapa
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-truck fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hay envíos en tránsito</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Mapa General (si hay envíos en tránsito) -->
                    @if($enviosEnTransito->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="bi bi-map"></i> Mapa de Envíos Activos</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="mapa-general" style="height: 400px;"></div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Panel Lateral -->
                <div class="col-lg-4">
                    
                    <!-- Incidentes Pendientes -->
                    <div class="card mb-4 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Incidentes Pendientes ({{ $incidentesPendientes->count() }})
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            @forelse($incidentesPendientes as $incidente)
                                <a href="{{ route('envios-planta.incidente-show', $incidente) }}" 
                                   class="list-group-item list-group-item-action list-group-item-danger">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $incidente->tipo_icono }} {{ $incidente->tipo_texto }}</strong>
                                        <small>{{ $incidente->fecha_reporte?->diffForHumans() }}</small>
                                    </div>
                                    <small>Envío: {{ $incidente->envioPlanta->codigo ?? 'N/A' }}</small>
                                </a>
                            @empty
                                <div class="text-center py-3">
                                    <i class="bi bi-check-circle text-success fs-3"></i>
                                    <p class="text-muted mb-0">Sin incidentes pendientes</p>
                                </div>
                            @endforelse
                        </div>
                        @if($incidentesPendientes->count() > 0)
                            <div class="card-footer">
                                <a href="{{ route('envios-planta.incidentes') }}" class="btn btn-danger btn-sm w-100">
                                    Ver Todos los Incidentes
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Próximos Envíos -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-clock-history"></i> 
                                Pendientes de Llegada
                            </h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            @forelse($enviosPendientes as $envio)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $envio->codigo }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $envio->almacen->nombre ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-end">
                                        {!! $envio->estado_badge !!}
                                        <br>
                                        <small>{{ $envio->fecha_estimada_entrega?->format('d/m') ?? '-' }}</small>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted">
                                    Sin envíos pendientes
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Últimos Entregados -->
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-check-circle"></i> 
                                Últimos Entregados
                            </h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            @forelse($ultimosEntregados as $envio)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $envio->codigo }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $envio->almacen->nombre ?? 'N/A' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <a href="{{ route('envios-planta.nota-recepcion', $envio) }}" 
                                           class="btn btn-sm btn-success">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                        <br>
                                        <small>{{ $envio->fecha_entrega?->format('d/m H:i') ?? '-' }}</small>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted">
                                    Sin entregas recientes
                                </li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Enlaces Rápidos -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('envios-planta.index') }}" class="btn btn-primary">
                            <i class="bi bi-list-ul"></i> Ver Todos los Envíos
                        </a>
                        <a href="{{ route('envios-planta.incidentes') }}" class="btn btn-outline-danger">
                            <i class="bi bi-exclamation-triangle"></i> Gestionar Incidentes
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</main>

@if($enviosEnTransito->count() > 0)
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const enviosEnTransito = @json($enviosEnTransito);
    
    if (enviosEnTransito.length > 0) {
        const mapa = L.map('mapa-general').setView([-17.7833, -63.1821], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapa);

        const bounds = [];

        enviosEnTransito.forEach(envio => {
            if (envio.ubicacion_lat && envio.ubicacion_lng) {
                const marker = L.marker([envio.ubicacion_lat, envio.ubicacion_lng])
                    .addTo(mapa)
                    .bindPopup(`
                        <strong>🚚 ${envio.codigo}</strong><br>
                        Transportista: ${envio.transportista_nombre || 'N/A'}<br>
                        Vehículo: ${envio.vehiculo_placa || 'N/A'}
                    `);
                bounds.push([envio.ubicacion_lat, envio.ubicacion_lng]);
            } else if (envio.destino_lat && envio.destino_lng) {
                bounds.push([envio.destino_lat, envio.destino_lng]);
            }
        });

        if (bounds.length > 0) {
            mapa.fitBounds(bounds, { padding: [50, 50] });
        }
    }
</script>
@endpush
@endif

@endsection

