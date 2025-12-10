@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-geo-alt-fill text-danger"></i> 
                        Monitoreo en Tiempo Real
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('envios-planta.index') }}">Envíos Planta</a></li>
                        <li class="breadcrumb-item active">Monitoreo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <div class="row">
                <!-- Mapa -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-map"></i> Ubicación del Envío
                            </h5>
                            <div>
                                <span id="estado-conexion" class="badge bg-success">
                                    <i class="bi bi-wifi"></i> Conectado
                                </span>
                                <button id="btn-actualizar" class="btn btn-sm btn-light ms-2">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div id="mapa" style="height: 500px; width: 100%;"></div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-clock"></i> 
                                    Última actualización: <span id="ultima-actualizacion">-</span>
                                </div>
                                <div>
                                    <span class="badge bg-primary me-2">
                                        <i class="bi bi-geo-alt"></i> Origen
                                    </span>
                                    <span class="badge bg-success me-2">
                                        <i class="bi bi-geo-alt-fill"></i> Destino
                                    </span>
                                    <span class="badge bg-danger">
                                        <i class="bi bi-truck"></i> Vehículo
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info del Envío -->
                <div class="col-lg-4">
                    
                    <!-- Estado Actual -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="bi bi-truck"></i> Estado del Envío</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="fs-1 mb-2">{{ $envioPlanta->estado_icono }}</div>
                            {!! $envioPlanta->estado_badge !!}
                            <h4 class="mt-3">{{ $envioPlanta->codigo }}</h4>
                        </div>
                    </div>

                    <!-- Transportista -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-person"></i> Transportista</h6>
                        </div>
                        <div class="card-body">
                            <h5>{{ $envioPlanta->transportista_nombre ?? 'Sin asignar' }}</h5>
                            @if($envioPlanta->transportista_telefono)
                                <a href="tel:{{ $envioPlanta->transportista_telefono }}" class="btn btn-success btn-sm">
                                    <i class="bi bi-telephone"></i> {{ $envioPlanta->transportista_telefono }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Vehículo -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-truck-front"></i> Vehículo</h6>
                        </div>
                        <div class="card-body">
                            <h5>{{ $envioPlanta->vehiculo_placa ?? 'Sin asignar' }}</h5>
                            <p class="text-muted mb-0">{{ $envioPlanta->vehiculo_descripcion ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Destino -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bi bi-geo-alt"></i> Destino</h6>
                        </div>
                        <div class="card-body">
                            <h5>{{ $envioPlanta->almacen->nombre ?? 'Almacén' }}</h5>
                            <p class="text-muted mb-0">
                                <i class="bi bi-pin-map"></i> {{ $envioPlanta->destino_direccion ?? 'Sin dirección' }}
                            </p>
                        </div>
                    </div>

                    <!-- Productos Resumidos -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-box"></i> Contenido</h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Productos:</span>
                                <strong>{{ $envioPlanta->productos->count() }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Cantidad Total:</span>
                                <strong>{{ $envioPlanta->total_cantidad }} unid.</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Peso Total:</span>
                                <strong>{{ number_format($envioPlanta->total_peso, 2) }} kg</strong>
                            </li>
                        </ul>
                    </div>

                    <!-- Volver -->
                    <div class="d-grid">
                        <a href="{{ route('envios-planta.show', $envioPlanta) }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Detalle
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</main>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

@endsection

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Coordenadas
    const origen = {
        lat: {{ $envioPlanta->origen_lat ?? -17.7833 }},
        lng: {{ $envioPlanta->origen_lng ?? -63.1821 }},
        direccion: '{{ $envioPlanta->origen_direccion ?? "Planta" }}'
    };
    
    const destino = {
        lat: {{ $envioPlanta->destino_lat ?? -17.7892 }},
        lng: {{ $envioPlanta->destino_lng ?? -63.1751 }},
        direccion: '{{ $envioPlanta->destino_direccion ?? "Destino" }}'
    };

    let ubicacionActual = {
        lat: {{ $envioPlanta->ubicacion_lat ?? $envioPlanta->origen_lat ?? -17.7833 }},
        lng: {{ $envioPlanta->ubicacion_lng ?? $envioPlanta->origen_lng ?? -63.1821 }}
    };

    // Inicializar mapa
    const mapa = L.map('mapa').setView([origen.lat, origen.lng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapa);

    // Iconos personalizados
    const iconoOrigen = L.divIcon({
        html: '<i class="bi bi-geo-alt-fill text-primary" style="font-size: 30px;"></i>',
        className: 'custom-icon',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    const iconoDestino = L.divIcon({
        html: '<i class="bi bi-geo-alt-fill text-success" style="font-size: 30px;"></i>',
        className: 'custom-icon',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    const iconoVehiculo = L.divIcon({
        html: '<i class="bi bi-truck text-danger" style="font-size: 35px; background: white; border-radius: 50%; padding: 5px;"></i>',
        className: 'custom-icon',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    // Marcadores
    L.marker([origen.lat, origen.lng], { icon: iconoOrigen })
        .addTo(mapa)
        .bindPopup('<strong>🏭 Origen:</strong><br>' + origen.direccion);

    L.marker([destino.lat, destino.lng], { icon: iconoDestino })
        .addTo(mapa)
        .bindPopup('<strong>📍 Destino:</strong><br>' + destino.direccion);

    // Marcador del vehículo
    const marcadorVehiculo = L.marker([ubicacionActual.lat, ubicacionActual.lng], { icon: iconoVehiculo })
        .addTo(mapa)
        .bindPopup('<strong>🚚 Vehículo</strong><br>{{ $envioPlanta->vehiculo_placa ?? "En camino" }}');

    // Línea de ruta
    const ruta = L.polyline([
        [origen.lat, origen.lng],
        [ubicacionActual.lat, ubicacionActual.lng],
        [destino.lat, destino.lng]
    ], {
        color: '#3388ff',
        weight: 4,
        opacity: 0.7,
        dashArray: '10, 10'
    }).addTo(mapa);

    // Ajustar vista al área de la ruta
    mapa.fitBounds(ruta.getBounds(), { padding: [50, 50] });

    // Actualizar ubicación
    function actualizarUbicacion() {
        fetch('{{ route("envios-planta.ubicacion", $envioPlanta) }}')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.lat && data.lng) {
                    ubicacionActual = { lat: data.lat, lng: data.lng };
                    marcadorVehiculo.setLatLng([data.lat, data.lng]);
                    
                    // Actualizar ruta
                    ruta.setLatLngs([
                        [origen.lat, origen.lng],
                        [data.lat, data.lng],
                        [destino.lat, destino.lng]
                    ]);

                    document.getElementById('ultima-actualizacion').textContent = data.actualizado_at || new Date().toLocaleTimeString();
                    document.getElementById('estado-conexion').className = 'badge bg-success';
                    document.getElementById('estado-conexion').innerHTML = '<i class="bi bi-wifi"></i> Conectado';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('estado-conexion').className = 'badge bg-danger';
                document.getElementById('estado-conexion').innerHTML = '<i class="bi bi-wifi-off"></i> Sin conexión';
            });
    }

    // Botón actualizar
    document.getElementById('btn-actualizar').addEventListener('click', actualizarUbicacion);

    // Actualizar cada 30 segundos
    setInterval(actualizarUbicacion, 30000);

    // Primera actualización
    actualizarUbicacion();
</script>

<style>
.custom-icon {
    background: transparent !important;
    border: none !important;
}
</style>
@endpush

