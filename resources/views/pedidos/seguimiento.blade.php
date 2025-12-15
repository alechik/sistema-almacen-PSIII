@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Seguimiento de Mis Pedidos</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pedidos.index') }}">Pedidos</a></li>
                        <li class="breadcrumb-item active">Seguimiento</li>
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
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-geo-alt-fill me-2"></i>
                                Seguimiento en Tiempo Real
                            </h5>
                            <div>
                                <span id="ultimo-update" class="badge bg-light text-dark me-2">Última actualización: --</span>
                                <span id="estado-conexion" class="badge bg-success"><i class="bi bi-circle-fill"></i> Conectado</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Lista de Envíos -->
                                <div class="col-md-4">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="bi bi-list-ul me-1"></i> Mis Pedidos en Tránsito</h6>
                                            <button class="btn btn-sm btn-light" onclick="actualizarEnvios()" title="Actualizar ahora">
                                                <i class="bi bi-arrow-clockwise" id="btn-sync-icon"></i>
                                            </button>
                                        </div>
                                        <div class="card-body" id="lista-envios" style="max-height: 600px; overflow-y: auto;">
                                            <!-- Se carga dinámicamente -->
                                            <div class="text-center py-4">
                                                <i class="bi bi-arrow-repeat fa-spin fa-2x text-primary"></i>
                                                <p class="mt-2">Cargando envíos...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mapa -->
                                <div class="col-md-8">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="bi bi-map me-1"></i> Mapa de Rutas en Tiempo Real</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="info-panel" class="alert alert-info mb-3">
                                                <i class="bi bi-info-circle"></i> Los envíos en tránsito hacia tu almacén se mostrarán automáticamente cuando el transportista inicie la ruta desde la app
                                            </div>
                                            <div id="map" style="height: 500px; border-radius: 8px;"></div>
                                        </div>
                                    </div>

                                    <!-- Panel de Control -->
                                    <div class="card shadow-sm mt-3" id="control-panel" style="display: none;">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0"><i class="bi bi-truck me-1"></i> Seguimiento Activo</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <h6>Envío: <span id="envio-codigo">-</span></h6>
                                                    <p class="mb-0">Estado: <span id="envio-estado" class="badge bg-info">-</span></p>
                                                    <p class="mb-0 mt-2"><small>Progreso: <span id="progreso-texto">0%</span></small></p>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <button class="btn btn-secondary btn-sm" onclick="cerrarSeguimiento()">
                                                        <i class="bi bi-x"></i> Cerrar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="progress mt-3" style="height: 25px;">
                                                <div id="progress-bar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                                     role="progressbar" style="width: 0%">0%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .envio-card {
        cursor: pointer;
        transition: all 0.3s;
    }
    .envio-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transform: translateY(-2px);
    }
    .envio-card.activo {
        border: 3px solid #ffc107 !important;
    }
    .leaflet-container {
        font-family: inherit;
    }
    .nuevo-envio {
        animation: highlight 2s ease-out;
    }
    @keyframes highlight {
        0% { background-color: #ffeb3b; }
        100% { background-color: inherit; }
    }
</style>
@endpush

@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.socket.io/4.6.0/socket.io.min.js"></script>
<script>
// Configuración
const PLANTA_COORDS = [-17.783333, -63.182778];
const INTERVALO_ACTUALIZACION = 10000; // 10 segundos como backup (WebSocket es principal)
const SOCKET_URL = 'http://192.168.0.129:3001/tracking'; // WebSocket server (Node.js)
const PLANTA_CRUDS_API_URL = '{{ $plantaCrudsApiUrl }}';

// IDs de envíos de los pedidos del usuario (filtro)
const PEDIDO_ENVIO_IDS = @json($pedidoEnvioIds ?? []);

// Variables globales
let map;
let marcadores = {};
let rutasPolylines = {};
let envioSeleccionado = null;
let intervaloActualizacion = null;
let ultimosEnviosIds = new Set();
let seguimientoCache = {};
let indiceAnimacion = {};
let socket = null;
let rutasCompletas = {};
let rutasOSRM = {};
let posicionesWebSocket = {};
let ultimaActualizacionWS = {};
let ultimoProgresoWS = {};
let intervaloProgreso = null;

// Obtener ruta desde seguimiento_envio (puntos reales de OSRM guardados)
async function obtenerRutaDesdeSeguimiento(envioId) {
    try {
        const response = await fetch(`${PLANTA_CRUDS_API_URL}/api/envios/${envioId}/seguimiento`);
        if (response.ok) {
            const data = await response.json();
            if (data && data.data && data.data.length > 0) {
                const puntos = data.data.map(p => [parseFloat(p.latitud), parseFloat(p.longitud)]);
                if (puntos.length > 1) {
                    console.log(`✅ Ruta obtenida desde seguimiento_envio: ${puntos.length} puntos`);
                    return puntos;
                }
            }
        }
    } catch (error) {
        console.warn('⚠️ Error obteniendo ruta desde seguimiento:', error);
    }
    return null;
}

// Obtener ruta real usando OSRM - RUTA REAL POR CALLES
async function obtenerRutaOSRM(origen, destino) {
    const cacheKey = `${origen[0]},${origen[1]}-${destino[0]},${destino[1]}`;
    if (rutasOSRM[cacheKey]) {
        console.log(`✅ Ruta OSRM desde cache: ${rutasOSRM[cacheKey].length} puntos`);
        return rutasOSRM[cacheKey];
    }
    
    try {
        console.log(`🔄 Obteniendo ruta OSRM desde (${origen[0]}, ${origen[1]}) hasta (${destino[0]}, ${destino[1]})`);
        
        // OSRM usa formato [lng, lat] para las coordenadas
        const url = `https://router.project-osrm.org/route/v1/driving/${origen[1]},${origen[0]};${destino[1]},${destino[0]}?overview=full&geometries=geojson&steps=true&alternatives=false`;
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
            const route = data.routes[0];
            const coordinates = route.geometry.coordinates;
            
            if (!coordinates || coordinates.length === 0) {
                throw new Error('OSRM devolvió ruta sin coordenadas');
            }
            
            // Convertir coordenadas GeoJSON [lng, lat] a formato Leaflet [lat, lng]
            const validCoordinates = coordinates
                .filter(coord => 
                    Array.isArray(coord) && 
                    coord.length >= 2 && 
                    typeof coord[0] === 'number' && 
                    typeof coord[1] === 'number' &&
                    !isNaN(coord[0]) && 
                    !isNaN(coord[1]) &&
                    coord[0] !== 0 && 
                    coord[1] !== 0
                )
                .map(coord => [coord[1], coord[0]]); // [lng, lat] -> [lat, lng]
            
            if (validCoordinates.length === 0) {
                throw new Error('No se pudieron convertir coordenadas de OSRM');
            }
            
            rutasOSRM[cacheKey] = validCoordinates;
            console.log(`✅ Ruta OSRM obtenida: ${validCoordinates.length} puntos válidos`);
            
            return validCoordinates;
        } else {
            const errorMsg = data.code || data.message || 'unknown';
            throw new Error(`OSRM error: ${errorMsg}`);
        }
    } catch (error) {
        console.error('❌ Error obteniendo ruta OSRM:', error);
        console.warn('⚠️ Usando interpolación como último recurso (línea recta)');
        
        // Último fallback: línea recta con más puntos interpolados
        const puntos = [];
        for (let i = 0; i <= 100; i++) {
            const lat = origen[0] + (destino[0] - origen[0]) * (i / 100);
            const lng = origen[1] + (destino[1] - origen[1]) * (i / 100);
            puntos.push([lat, lng]);
        }
        console.warn(`⚠️ Ruta interpolada generada: ${puntos.length} puntos (línea recta)`);
        return puntos;
    }
}

// Iconos personalizados
const iconos = {
    planta: L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    }),
    destino: L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
    }),
    vehiculo: L.divIcon({
        html: '<div style="background: #2196F3; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"><i class="bi bi-truck" style="color: white; font-size: 14px;"></i></div>',
        className: 'custom-truck-icon',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    })
};

// Inicializar WebSocket
function inicializarWebSocket() {
    try {
        socket = io(SOCKET_URL, {
            transports: ['websocket', 'polling'],
            reconnection: true,
            reconnectionAttempts: 10,
            reconnectionDelay: 1000
        });

        socket.on('connect', () => {
            console.log('🔌 WebSocket conectado');
            document.getElementById('estado-conexion').className = 'badge bg-success';
            document.getElementById('estado-conexion').innerHTML = '<i class="bi bi-circle-fill"></i> WebSocket Conectado';
            // Reconectar todos los envíos activos
            Object.keys(marcadores).forEach(envioId => {
                socket.emit('join', `envio-${envioId}`);
            });
        });

        socket.on('disconnect', () => {
            console.log('❌ WebSocket desconectado');
            document.getElementById('estado-conexion').className = 'badge bg-warning';
            document.getElementById('estado-conexion').innerHTML = '<i class="bi bi-exclamation-circle"></i> Reconectando...';
        });
        
        socket.on('connect_error', (error) => {
            console.warn('⚠️ Error de conexión WebSocket:', error);
            document.getElementById('estado-conexion').className = 'badge bg-danger';
            document.getElementById('estado-conexion').innerHTML = '<i class="bi bi-exclamation-triangle"></i> Sin WebSocket (usando polling)';
        });

        socket.on('simulacion-iniciada', async (data) => {
            console.log('🚀 Simulación iniciada:', data);
            const { envioId, rutaPuntos } = data;
            
            // FILTRAR: Solo procesar si este envío pertenece a nuestros pedidos
            if (!PEDIDO_ENVIO_IDS.includes(parseInt(envioId))) {
                console.log(`⏭️ Envío ${envioId} no pertenece a nuestros pedidos, ignorando...`);
                return;
            }
            
            posicionesWebSocket[envioId] = [];
            ultimaActualizacionWS[envioId] = Date.now();
            ultimoProgresoWS[envioId] = 0;
            
            if (marcadores[envioId]) {
                if (marcadores[envioId].vehiculo) map.removeLayer(marcadores[envioId].vehiculo);
                if (marcadores[envioId].destino) map.removeLayer(marcadores[envioId].destino);
                if (marcadores[envioId].ruta) map.removeLayer(marcadores[envioId].ruta);
                if (marcadores[envioId].rutaRecorrida) map.removeLayer(marcadores[envioId].rutaRecorrida);
                delete marcadores[envioId];
            }
            
            if (rutaPuntos && rutaPuntos.length > 0) {
                const rutaLeaflet = rutaPuntos.map(punto => {
                    const lat = punto.latitude || punto.lat;
                    const lng = punto.longitude || punto.lng;
                    return [lat, lng];
                }).filter(p => p[0] && p[1]);
                
                rutasCompletas[envioId] = rutaLeaflet;
                
                if (rutaLeaflet.length > 0) {
                    posicionesWebSocket[envioId] = [rutaLeaflet[0]];
                    const primerPunto = rutaLeaflet[0];
                    const ultimoPunto = rutaLeaflet[rutaLeaflet.length - 1];
                    
                    const marcadorDestino = L.marker(ultimoPunto, { icon: iconos.destino })
                        .addTo(map)
                        .bindPopup(`<b>📦 Destino</b><br>Envío ${envioId}`);
                    
                    const marcadorVehiculo = L.marker(primerPunto, { icon: iconos.vehiculo })
                        .addTo(map)
                        .bindPopup(`<b>🚚 Envío ${envioId}</b><br>Iniciando ruta...`);
                    
                    const lineaRutaCompleta = L.polyline(rutaLeaflet, {
                        color: '#2196F3',
                        weight: 5,
                        opacity: 0.5,
                        dashArray: '10, 10',
                        smoothFactor: 1.0
                    }).addTo(map);
                    
                    const lineaRutaRecorrida = L.polyline([primerPunto], {
                        color: '#4CAF50',
                        weight: 6,
                        opacity: 0.9,
                        smoothFactor: 1.0
                    }).addTo(map);
                    
                    marcadores[envioId] = { 
                        vehiculo: marcadorVehiculo, 
                        destino: marcadorDestino,
                        ruta: lineaRutaCompleta,
                        rutaRecorrida: lineaRutaRecorrida
                    };
                    
                    map.fitBounds(L.latLngBounds(rutaLeaflet), { padding: [50, 50] });
                }
            }
            
            socket.emit('join', `envio-${envioId}`);
            mostrarNotificacion(`🚚 Envío ${envioId} ha iniciado la ruta`);
            actualizarEnvios();
        });

        socket.on('posicion-actualizada', (data) => {
            const { envioId, posicion, progreso } = data;
            
            // FILTRAR: Solo procesar si este envío pertenece a nuestros pedidos
            if (!PEDIDO_ENVIO_IDS.includes(parseInt(envioId))) {
                return;
            }
            
            if (envioId && posicion && progreso !== undefined) {
                actualizarPosicionCamion(envioId, posicion, progreso);
            }
        });

        socket.on('envio-completado', (data) => {
            const { envioId } = data;
            
            // FILTRAR: Solo procesar si este envío pertenece a nuestros pedidos
            if (!PEDIDO_ENVIO_IDS.includes(parseInt(envioId))) {
                return;
            }
            
            mostrarNotificacion(`✅ Envío ${envioId} ha llegado a su destino`);
            
            if (marcadores[envioId]) {
                if (marcadores[envioId].vehiculo) map.removeLayer(marcadores[envioId].vehiculo);
                if (marcadores[envioId].ruta) map.removeLayer(marcadores[envioId].ruta);
                if (marcadores[envioId].rutaRecorrida) map.removeLayer(marcadores[envioId].rutaRecorrida);
            }
            
            delete posicionesWebSocket[envioId];
            delete ultimaActualizacionWS[envioId];
            delete ultimoProgresoWS[envioId];
            delete rutasCompletas[envioId];
            delete seguimientoCache[envioId];
            
            actualizarEnvios();
        });

    } catch (error) {
        console.error('Error inicializando WebSocket:', error);
    }
}

function actualizarPosicionCamion(envioId, posicion, progreso) {
    const lat = posicion.latitude || posicion.lat;
    const lng = posicion.longitude || posicion.lng;
    if (!lat || !lng) return;
    
    if (ultimoProgresoWS[envioId] !== undefined && progreso < ultimoProgresoWS[envioId] - 0.05) {
        return;
    }
    
    const nuevaPosicion = [lat, lng];
    if (!posicionesWebSocket[envioId]) {
        posicionesWebSocket[envioId] = [];
    }
    
    const ultimaPosicion = posicionesWebSocket[envioId][posicionesWebSocket[envioId].length - 1];
    if (!ultimaPosicion || 
        Math.abs(ultimaPosicion[0] - nuevaPosicion[0]) > 0.00001 || 
        Math.abs(ultimaPosicion[1] - nuevaPosicion[1]) > 0.00001) {
        posicionesWebSocket[envioId].push(nuevaPosicion);
    }
    
    ultimaActualizacionWS[envioId] = Date.now();
    ultimoProgresoWS[envioId] = progreso;
    
    if (marcadores[envioId] && marcadores[envioId].vehiculo) {
        marcadores[envioId].vehiculo.setLatLng(nuevaPosicion);
        if (marcadores[envioId].rutaRecorrida && posicionesWebSocket[envioId].length > 0) {
            marcadores[envioId].rutaRecorrida.setLatLngs(posicionesWebSocket[envioId]);
        }
        marcadores[envioId].vehiculo.setPopupContent(
            `<b>🚚 Envío ${envioId}</b><br>Progreso: ${Math.round(progreso * 100)}%<br><small>🔴 En vivo</small>`
        );
    }
    
    const progressBar = document.getElementById(`progress-${envioId}`);
    const progressText = document.getElementById(`progress-text-${envioId}`);
    if (progressBar) {
        const progresoPercent = Math.round(progreso * 100);
        progressBar.style.width = progresoPercent + '%';
        if (progressText) {
            progressText.textContent = progresoPercent + '% completado';
        }
    }
    
    if (envioSeleccionado == envioId) {
        const progresoPercent = Math.round(progreso * 100);
        const mainProgressBar = document.getElementById('progress-bar');
        const mainProgressText = document.getElementById('progreso-texto');
        if (mainProgressBar) {
            mainProgressBar.style.width = progresoPercent + '%';
            mainProgressBar.textContent = progresoPercent + '%';
        }
        if (mainProgressText) {
            mainProgressText.textContent = progresoPercent + '%';
        }
    }
}

function mostrarNotificacion(mensaje) {
    const container = document.getElementById('lista-envios');
    const notif = document.createElement('div');
    notif.className = 'alert alert-info alert-dismissible fade show';
    notif.innerHTML = `${mensaje} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    container.insertBefore(notif, container.firstChild);
    setTimeout(() => {
        if (notif.parentNode) notif.remove();
    }, 5000);
}

function inicializarMapa() {
    try {
        const mapElement = document.getElementById('map');
        if (!mapElement) {
            console.error('❌ Elemento #map no encontrado');
            return;
        }
        
        console.log('🗺️ Inicializando mapa...');
        map = L.map('map').setView(PLANTA_COORDS, 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 18,
        }).addTo(map);
        
        L.marker(PLANTA_COORDS, { icon: iconos.planta })
            .addTo(map)
            .bindPopup('<b>🏭 Planta - Origen</b><br>Santa Cruz de la Sierra');
        
        console.log('✅ Mapa inicializado correctamente');
    } catch (error) {
        console.error('❌ Error inicializando mapa:', error);
    }
}

function iniciarActualizacionAutomatica() {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
    intervaloActualizacion = setInterval(actualizarEnvios, INTERVALO_ACTUALIZACION);
    
    if (intervaloProgreso) clearInterval(intervaloProgreso);
    intervaloProgreso = setInterval(() => {
        if (!socket || !socket.connected) {
            actualizarProgresoEnviosActivos();
        }
    }, 2000);
}

// Inicializar cuando el DOM esté listo (al final del script, después de definir todas las funciones)
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Inicializando seguimiento...');
    console.log('📦 Envíos a filtrar:', PEDIDO_ENVIO_IDS);
    console.log('🔗 API URL:', PLANTA_CRUDS_API_URL);
    
    // Verificar que Leaflet esté cargado
    if (typeof L === 'undefined') {
        console.error('❌ Leaflet no está cargado');
        return;
    }
    
    try {
        inicializarMapa();
        console.log('✅ Mapa inicializado');
    } catch (error) {
        console.error('❌ Error inicializando mapa:', error);
    }
    
    try {
        inicializarWebSocket();
        console.log('✅ WebSocket inicializado');
    } catch (error) {
        console.error('❌ Error inicializando WebSocket:', error);
    }
    
    // Esperar un momento para asegurar que todas las funciones estén definidas
    setTimeout(() => {
        try {
            if (typeof window.actualizarEnvios === 'function') {
                window.actualizarEnvios();
                console.log('✅ Envíos actualizados');
            } else {
                console.error('❌ actualizarEnvios no está definida');
            }
        } catch (error) {
            console.error('❌ Error actualizando envíos:', error);
        }
    }, 100);
    
    try {
        iniciarActualizacionAutomatica();
        console.log('✅ Actualización automática iniciada');
    } catch (error) {
        console.error('❌ Error iniciando actualización automática:', error);
    }
});

function actualizarProgresoEnviosActivos() {
    Object.keys(marcadores).forEach(envioId => {
        const envioCard = document.querySelector(`[data-envio-id="${envioId}"]`);
        if (envioCard) {
            if (ultimoProgresoWS[envioId] !== undefined) {
                const progreso = ultimoProgresoWS[envioId];
                const progressBar = document.getElementById(`progress-${envioId}`);
                const progressText = document.getElementById(`progress-text-${envioId}`);
                if (progressBar) {
                    const percent = Math.round(progreso * 100);
                    progressBar.style.width = percent + '%';
                    if (progressText) {
                        progressText.textContent = percent + '% completado';
                    }
                }
            } else {
                const fechaInicio = envioCard.dataset.fechaInicio;
                if (fechaInicio) {
                    const progreso = calcularProgreso(envioId, fechaInicio);
                    const progressBar = document.getElementById(`progress-${envioId}`);
                    const progressText = document.getElementById(`progress-text-${envioId}`);
                    if (progressBar) {
                        const percent = Math.round(progreso * 100);
                        progressBar.style.width = percent + '%';
                        if (progressText) {
                            progressText.textContent = percent + '% completado';
                        }
                    }
                }
            }
        }
    });
}

// Hacer la función global para que sea accesible desde onclick
window.actualizarEnvios = async function() {
    const btnIcon = document.getElementById('btn-sync-icon');
    if (btnIcon) {
        btnIcon.classList.add('fa-spin');
        btnIcon.classList.add('spinner-border');
    }
    
    try {
        console.log('🔄 Actualizando envíos...');
        console.log('📋 IDs de envíos a consultar:', PEDIDO_ENVIO_IDS);
        
        if (!PEDIDO_ENVIO_IDS || PEDIDO_ENVIO_IDS.length === 0) {
            console.warn('⚠️ No hay IDs de envíos para consultar');
            renderizarListaEnvios([], [], []);
            return;
        }
        
        // Obtener TODOS los envíos (activos y entregados) desde plantaCruds usando el nuevo endpoint
        const response = await fetch(`${PLANTA_CRUDS_API_URL}/api/rutas/envios-por-ids`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: PEDIDO_ENVIO_IDS })
        });
        
        if (!response.ok) {
            throw new Error(`Error en respuesta: ${response.status} ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📦 Envíos recibidos:', data);
        
        // Ya vienen filtrados por IDs, no necesitamos filtrar de nuevo
        const enviosFiltrados = {
            en_transito: data.en_transito || [],
            esperando: data.esperando || [],
            entregados: data.entregados || []
        };
        
        console.log('✅ Envíos filtrados:', enviosFiltrados);
        console.log(`📊 Resumen: ${enviosFiltrados.en_transito.length} en tránsito, ${enviosFiltrados.esperando.length} esperando, ${enviosFiltrados.entregados.length} entregados`);
        
        renderizarListaEnvios(enviosFiltrados.en_transito || [], enviosFiltrados.esperando || [], enviosFiltrados.entregados || []);
        await actualizarMapaConEnvios(enviosFiltrados.en_transito || []);
        
        const ahora = new Date();
        const updateElement = document.getElementById('ultimo-update');
        if (updateElement) {
            updateElement.textContent = 'Última actualización: ' + ahora.toLocaleTimeString();
        }
        
        const estadoElement = document.getElementById('estado-conexion');
        if (estadoElement) {
            estadoElement.className = 'badge bg-success';
            estadoElement.innerHTML = '<i class="bi bi-circle-fill"></i> Conectado';
        }
        
    } catch (error) {
        console.error('❌ Error actualizando envíos:', error);
        const estadoElement = document.getElementById('estado-conexion');
        if (estadoElement) {
            estadoElement.className = 'badge bg-danger';
            estadoElement.innerHTML = '<i class="bi bi-exclamation-circle"></i> Error: ' + error.message;
        }
    } finally {
        if (btnIcon) {
            btnIcon.classList.remove('fa-spin');
            btnIcon.classList.remove('spinner-border');
        }
    }
};

function renderizarListaEnvios(enTransito, esperando, entregados = []) {
    const container = document.getElementById('lista-envios');
    let html = '';
    
    html += `<h6 class="text-info mt-2"><i class="bi bi-truck"></i> En Tránsito (${enTransito.length})</h6>`;
    
    if (enTransito.length === 0) {
        html += `<div class="alert alert-secondary py-2"><i class="bi bi-info-circle"></i> No hay envíos en tránsito hacia tu almacén</div>`;
    } else {
        enTransito.forEach(envio => {
            const esNuevo = !ultimosEnviosIds.has(envio.id);
            const claseNuevo = esNuevo ? 'nuevo-envio' : '';
            ultimosEnviosIds.add(envio.id);
            
            const progreso = calcularProgreso(envio.id, envio.fecha_inicio_transito);
            
            html += `
                <div class="envio-card mb-2 p-3 border rounded bg-info text-white ${claseNuevo} ${envioSeleccionado == envio.id ? 'activo' : ''}" 
                     onclick="seleccionarEnvio(${envio.id}, '${envio.codigo}', ${envio.destino_lat || -17.78}, ${envio.destino_lng || -63.18}, this)"
                     data-envio-id="${envio.id}"
                     data-fecha-inicio="${envio.fecha_inicio_transito || ''}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-warning mb-1">🚚 EN RUTA</span>
                            <p class="mb-1"><strong>${envio.codigo}</strong></p>
                            <p class="mb-1 small">📦 ${envio.almacen_nombre || 'N/A'}</p>
                            <p class="mb-1 small">📍 Destino: ${envio.direccion_completa || 'N/A'}</p>
                            ${envio.transportista_nombre ? `<p class="mb-0 small">👤 ${envio.transportista_nombre}</p>` : ''}
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" id="progress-${envio.id}" style="width: ${Math.round(progreso * 100)}%"></div>
                            </div>
                            <small id="progress-text-${envio.id}">${Math.round(progreso * 100)}% completado</small>
                        </div>
                        <button class="btn btn-sm btn-light" onclick="event.stopPropagation(); verEnMapa(${envio.id}, '${envio.codigo}', ${envio.destino_lat || -17.78}, ${envio.destino_lng || -63.18})">
                            <i class="bi bi-geo-alt"></i>
                        </button>
                    </div>
                </div>
            `;
        });
    }
    
    html += `<h6 class="text-warning mt-3"><i class="bi bi-clock"></i> Esperando Inicio (${esperando.length})</h6>`;
    
    if (esperando.length === 0) {
        html += `<div class="alert alert-secondary py-2"><i class="bi bi-check-circle"></i> No hay envíos esperando</div>`;
    } else {
        esperando.forEach(envio => {
            const estadoClass = envio.estado === 'aceptado' ? 'success' : 'secondary';
            html += `
                <div class="envio-card mb-2 p-2 border rounded bg-light" style="opacity: 0.9;">
                    <span class="badge bg-${estadoClass}">${(envio.estado || '').toUpperCase()}</span>
                    <p class="mb-1 mt-1"><strong>${envio.codigo}</strong></p>
                    <p class="mb-0 small text-muted">📦 ${envio.almacen_nombre || 'N/A'}</p>
                    <small class="text-muted"><i class="bi bi-info-circle"></i> Esperando inicio del transportista</small>
                </div>
            `;
        });
    }
    
    // Agregar sección de envíos entregados
    html += `<h6 class="text-success mt-3"><i class="bi bi-check-circle-fill"></i> Entregados (${entregados.length})</h6>`;
    
    if (entregados.length === 0) {
        html += `<div class="alert alert-secondary py-2"><i class="bi bi-info-circle"></i> No hay envíos entregados aún</div>`;
    } else {
        entregados.forEach(envio => {
            const fechaEntrega = envio.fecha_entrega ? new Date(envio.fecha_entrega).toLocaleString('es-ES') : 'N/A';
            html += `
                <div class="envio-card mb-2 p-2 border rounded bg-light" style="opacity: 0.9;">
                    <span class="badge bg-success mb-1">✅ ENTREGADO</span>
                    <p class="mb-1 mt-1"><strong>${envio.codigo}</strong></p>
                    <p class="mb-1 small text-muted">📦 ${envio.almacen_nombre || 'N/A'}</p>
                    <p class="mb-1 small text-muted">📍 ${envio.direccion_completa || 'N/A'}</p>
                    ${envio.transportista_nombre ? `<p class="mb-1 small text-muted">👤 ${envio.transportista_nombre}</p>` : ''}
                    <small class="text-muted"><i class="bi bi-calendar-check"></i> Entregado: ${fechaEntrega}</small>
                </div>
            `;
        });
    }
    
    container.innerHTML = html;
}

let actualizandoMapa = false;

async function actualizarMapaConEnvios(enviosEnTransito) {
    if (actualizandoMapa) return;
    actualizandoMapa = true;
    
    try {
        for (const envio of enviosEnTransito) {
            const envioId = envio.id;
            
            // FILTRAR: Solo procesar envíos de nuestros pedidos
            if (!PEDIDO_ENVIO_IDS.includes(envioId)) {
                continue;
            }
            
            if (ultimaActualizacionWS[envioId] && (Date.now() - ultimaActualizacionWS[envioId]) < 5000) {
                continue;
            }
            
            if (marcadores[envioId] && marcadores[envioId].vehiculo && rutasCompletas[envioId]) {
                continue;
            }
            
            const tieneDataWebSocket = posicionesWebSocket[envioId] && posicionesWebSocket[envioId].length > 0;
            if (marcadores[envioId] && marcadores[envioId].vehiculo && tieneDataWebSocket) {
                continue;
            }
            
            if (marcadores[envioId]) {
                if (marcadores[envioId].vehiculo) map.removeLayer(marcadores[envioId].vehiculo);
                if (marcadores[envioId].destino) map.removeLayer(marcadores[envioId].destino);
                if (marcadores[envioId].ruta) map.removeLayer(marcadores[envioId].ruta);
                if (marcadores[envioId].rutaRecorrida) map.removeLayer(marcadores[envioId].rutaRecorrida);
            }
            
            const destinoLat = parseFloat(envio.destino_lat) || -17.78;
            const destinoLng = parseFloat(envio.destino_lng) || -63.18;
            const destino = [destinoLat, destinoLng];
            
            let rutaCompleta;
            if (rutasCompletas[envioId] && rutasCompletas[envioId].length > 0) {
                rutaCompleta = rutasCompletas[envioId];
            } else {
                const rutaSeguimiento = await obtenerRutaDesdeSeguimiento(envioId);
                if (rutaSeguimiento && rutaSeguimiento.length > 10) {
                    rutaCompleta = rutaSeguimiento;
                    rutasCompletas[envioId] = rutaCompleta;
                } else {
                    rutaCompleta = await obtenerRutaOSRM(PLANTA_COORDS, destino);
                    rutasCompletas[envioId] = rutaCompleta;
                }
            }
            
            const progreso = calcularProgreso(envioId, envio.fecha_inicio_transito);
            const indiceCamion = Math.max(0, Math.min(
                Math.floor(progreso * (rutaCompleta.length - 1)),
                rutaCompleta.length - 1
            ));
            
            let posActual = rutaCompleta[indiceCamion] || PLANTA_COORDS;
            let rutaRecorridaPuntos = rutaCompleta.slice(0, indiceCamion + 1);
            
            if (!posicionesWebSocket[envioId]) {
                posicionesWebSocket[envioId] = [posActual];
            }
            
            const marcadorDestino = L.marker(destino, { icon: iconos.destino })
                .addTo(map)
                .bindPopup(`<b>📦 ${envio.almacen_nombre}</b><br>${envio.direccion_completa || 'Destino del envío'}`);
            
            const marcadorVehiculo = L.marker(posActual, { icon: iconos.vehiculo })
                .addTo(map)
                .bindPopup(`<b>🚚 ${envio.codigo}</b><br>Progreso: ${Math.round(progreso * 100)}%<br>${envio.transportista_nombre || ''}<br>${envio.vehiculo_placa ? `Placa: ${envio.vehiculo_placa}` : ''}`);
            
            if (rutaCompleta.length < 3) {
                rutaCompleta = await obtenerRutaOSRM(PLANTA_COORDS, destino);
                rutasCompletas[envioId] = rutaCompleta;
            }
            
            const lineaRutaCompleta = L.polyline(rutaCompleta, {
                color: '#2196F3',
                weight: 5,
                opacity: 0.5,
                dashArray: '10, 10',
                smoothFactor: 1.0
            }).addTo(map);
            
            const lineaRutaRecorrida = L.polyline(rutaRecorridaPuntos, {
                color: '#4CAF50',
                weight: 6,
                opacity: 0.9,
                smoothFactor: 1.0
            }).addTo(map);
            
            marcadores[envioId] = { 
                vehiculo: marcadorVehiculo, 
                destino: marcadorDestino,
                ruta: lineaRutaCompleta,
                rutaRecorrida: lineaRutaRecorrida
            };
            
            if (envioSeleccionado == envioId) {
                document.getElementById('progress-bar').style.width = Math.round(progreso * 100) + '%';
                document.getElementById('progress-bar').textContent = Math.round(progreso * 100) + '%';
                document.getElementById('progreso-texto').textContent = Math.round(progreso * 100) + '%';
            }
        }
        
        if (enviosEnTransito.length > 0 && !envioSeleccionado) {
            const bounds = [PLANTA_COORDS];
            enviosEnTransito.forEach(e => {
                if (e.destino_lat && e.destino_lng) {
                    bounds.push([parseFloat(e.destino_lat), parseFloat(e.destino_lng)]);
                }
            });
            if (bounds.length > 1) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    } finally {
        actualizandoMapa = false;
    }
}

function calcularProgreso(envioId, fechaInicio) {
    if (ultimoProgresoWS[envioId] !== undefined) {
        return ultimoProgresoWS[envioId];
    }
    
    if (!fechaInicio) return 0;
    const inicio = new Date(fechaInicio).getTime();
    const ahora = Date.now();
    const duracionTotal = 60 * 1000;
    const transcurrido = ahora - inicio;
    return Math.min(1, Math.max(0, transcurrido / duracionTotal));
}

// Hacer funciones globales para que sean accesibles desde onclick
window.seleccionarEnvio = function(id, codigo, lat, lng, element) {
    envioSeleccionado = id;
    verEnMapa(id, codigo, lat, lng);
    document.querySelectorAll('.envio-card').forEach(card => card.classList.remove('activo'));
    if (element) element.classList.add('activo');
};

window.verEnMapa = function(id, codigo, lat, lng) {
    envioSeleccionado = id;
    const destino = [lat, lng];
    
    Object.keys(marcadores).forEach(envioId => {
        if (envioId != id) {
            if (marcadores[envioId].vehiculo) map.removeLayer(marcadores[envioId].vehiculo);
            if (marcadores[envioId].destino) map.removeLayer(marcadores[envioId].destino);
            if (marcadores[envioId].ruta) map.removeLayer(marcadores[envioId].ruta);
            if (marcadores[envioId].rutaRecorrida) map.removeLayer(marcadores[envioId].rutaRecorrida);
        }
    });
    
    if (marcadores[id]) {
        if (marcadores[id].vehiculo && !map.hasLayer(marcadores[id].vehiculo)) {
            marcadores[id].vehiculo.addTo(map);
        }
        if (marcadores[id].destino && !map.hasLayer(marcadores[id].destino)) {
            marcadores[id].destino.addTo(map);
        }
        if (marcadores[id].ruta && !map.hasLayer(marcadores[id].ruta)) {
            marcadores[id].ruta.addTo(map);
        }
        if (marcadores[id].rutaRecorrida && !map.hasLayer(marcadores[id].rutaRecorrida)) {
            marcadores[id].rutaRecorrida.addTo(map);
        }
    }
    
    if (marcadores[id] && marcadores[id].vehiculo) {
        const pos = marcadores[id].vehiculo.getLatLng();
        map.setView([pos.lat, pos.lng], 14);
        marcadores[id].vehiculo.openPopup();
    } else {
        map.setView(destino, 14);
    }
    
    document.getElementById('control-panel').style.display = 'block';
    document.getElementById('envio-codigo').textContent = codigo;
    document.getElementById('envio-estado').textContent = 'EN TRÁNSITO';
    document.getElementById('envio-estado').className = 'badge bg-info';
    document.getElementById('info-panel').innerHTML = 
        `<i class="bi bi-truck"></i> Siguiendo envío <strong>${codigo}</strong> en tiempo real - Actualizando cada 2 segundos`;
    document.getElementById('info-panel').className = 'alert alert-success mb-3';
    
    const envioCard = document.querySelector(`[data-envio-id="${id}"]`);
    let progreso = 0;
    if (ultimoProgresoWS[id] !== undefined) {
        progreso = ultimoProgresoWS[id];
    } else if (envioCard) {
        const fechaInicio = envioCard.dataset.fechaInicio;
        if (fechaInicio) {
            progreso = calcularProgreso(id, fechaInicio);
        }
    }
    
    const progresoPercent = Math.round(progreso * 100);
    const progressBar = document.getElementById('progress-bar');
    const progresoTexto = document.getElementById('progreso-texto');
    if (progressBar) {
        progressBar.style.width = progresoPercent + '%';
        progressBar.textContent = progresoPercent + '%';
    }
    if (progresoTexto) {
        progresoTexto.textContent = progresoPercent + '%';
    }
}

window.cerrarSeguimiento = function() {
    envioSeleccionado = null;
    const controlPanel = document.getElementById('control-panel');
    if (controlPanel) controlPanel.style.display = 'none';
    
    const infoPanel = document.getElementById('info-panel');
    if (infoPanel) {
        infoPanel.innerHTML = '<i class="bi bi-info-circle"></i> Los envíos en tránsito se mostrarán automáticamente';
        infoPanel.className = 'alert alert-info mb-3';
    }
    
    document.querySelectorAll('.envio-card').forEach(card => card.classList.remove('activo'));
    if (map) map.setView(PLANTA_COORDS, 13);
};

window.addEventListener('beforeunload', function() {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
    if (socket) socket.disconnect();
});
</script>
@endpush
