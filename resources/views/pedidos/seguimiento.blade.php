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
                                            <button class="btn btn-sm btn-light" onclick="window.actualizarEnvios()" title="Actualizar ahora">
                                                <i class="bi bi-arrow-clockwise" id="btn-sync-icon"></i>
                                            </button>
                                        </div>
                                        <div class="card-body" id="lista-envios" style="max-height: 600px; overflow-y: auto;">
                                            <!-- Se carga dinámicamente -->
                                            <div class="text-center py-4" id="loading-message">
                                                <i class="bi bi-arrow-repeat fa-spin fa-2x text-primary"></i>
                                                <p class="mt-2">Cargando envíos...</p>
                                                <small class="text-muted d-block mt-2" id="loading-details">Esperando datos...</small>
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
                                        <div class="card-body" style="padding: 15px;">
                                            <div id="info-panel" class="alert alert-info mb-3">
                                                <i class="bi bi-info-circle"></i> Los envíos en tránsito hacia tu almacén se mostrarán automáticamente cuando el transportista inicie la ruta desde la app
                                            </div>
                                            <div id="map" style="height: 600px; width: 100%; border-radius: 8px; position: relative; z-index: 1;"></div>
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
<link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet" />
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
    #map {
        width: 100%;
        height: 600px;
        position: relative;
        margin: 0;
        padding: 0;
    }
    .maplibregl-map {
        width: 100%;
        height: 100%;
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

@push('scripts')
<script src="https://cdn.socket.io/4.6.0/socket.io.min.js"></script>
<script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
<script>
// Configuración
const PLANTA_COORDS = [-17.783333, -63.182778];
const INTERVALO_ACTUALIZACION = 10000; // 10 segundos como backup (WebSocket es principal)
const SOCKET_URL = 'http://192.168.0.129:3001/tracking'; // WebSocket server (Node.js)
const PLANTA_CRUDS_API_URL = @json($plantaCrudsApiUrl ?? 'http://localhost:8001');

// IDs de envíos de los pedidos del usuario (filtro)
const PEDIDO_ENVIO_IDS = @json($pedidoEnvioIds ?? []);

// Variables globales
let mapaInicializado = false; // Flag para evitar inicializaciones múltiples
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

// DEFINIR actualizarEnvios ANTES de DOMContentLoaded para que esté disponible cuando se llame
window.actualizarEnvios = async function() {
    console.log('🚀 [actualizarEnvios] FUNCIÓN LLAMADA - Iniciando...');
    
    const btnIcon = document.getElementById('btn-sync-icon');
    const loadingMessage = document.getElementById('loading-message');
    const loadingDetails = document.getElementById('loading-details');
    const container = document.getElementById('lista-envios');
    
    console.log('🚀 [actualizarEnvios] Elementos DOM:', {
        btnIcon: !!btnIcon,
        loadingMessage: !!loadingMessage,
        loadingDetails: !!loadingDetails,
        container: !!container
    });
    
    if (btnIcon) {
        btnIcon.classList.add('fa-spin');
        btnIcon.classList.add('spinner-border');
    }
    
    if (loadingDetails) {
        loadingDetails.textContent = 'Consultando API...';
    }
    
    try {
        console.log('🔄 [actualizarEnvios] Actualizando envíos...');
        console.log('📋 IDs de envíos a consultar:', PEDIDO_ENVIO_IDS);
        console.log('📋 Tipo de PEDIDO_ENVIO_IDS:', typeof PEDIDO_ENVIO_IDS);
        console.log('📋 Es array:', Array.isArray(PEDIDO_ENVIO_IDS));
        console.log('📋 Longitud:', PEDIDO_ENVIO_IDS?.length);
        console.log('🔗 PLANTA_CRUDS_API_URL:', PLANTA_CRUDS_API_URL);
        
        if (!PEDIDO_ENVIO_IDS || PEDIDO_ENVIO_IDS.length === 0) {
            console.warn('⚠️ No hay IDs de envíos para consultar');
            console.warn('⚠️ PEDIDO_ENVIO_IDS:', PEDIDO_ENVIO_IDS);
            
            // Ocultar mensaje de carga
            if (loadingMessage) {
                loadingMessage.style.display = 'none';
            }
            
            // Mostrar mensaje de que no hay envíos
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>No hay pedidos con envíos asociados</strong><br>
                        <small>Los envíos aparecerán aquí cuando se asignen desde plantaCruds y estén en tránsito, asignados, entregados o cancelados.</small>
                        <br><br>
                        <small class="text-muted">Si crees que debería haber envíos, verifica que:</small>
                        <ul class="text-muted small">
                            <li>Los pedidos tengan envíos asignados en pedido_entregas</li>
                            <li>Los envíos estén en estado: en_transito, asignado, aceptado, entregado o cancelado</li>
                        </ul>
                    </div>
                `;
            }
            
            if (btnIcon) {
                btnIcon.classList.remove('fa-spin');
                btnIcon.classList.remove('spinner-border');
            }
            return;
        }
        
        const url = `${PLANTA_CRUDS_API_URL}/api/rutas/envios-por-ids`;
        console.log('🌐 Consultando envíos en:', url);
        console.log('📋 IDs a consultar:', PEDIDO_ENVIO_IDS);
        console.log('📋 Total IDs:', PEDIDO_ENVIO_IDS.length);
        
        if (loadingDetails) {
            loadingDetails.textContent = `Consultando ${PEDIDO_ENVIO_IDS.length} envío(s)...`;
        }
        
        // Verificar que la URL esté configurada
        const apiUrl = PLANTA_CRUDS_API_URL ? PLANTA_CRUDS_API_URL.trim() : '';
        if (!apiUrl || apiUrl === '' || apiUrl === 'undefined' || apiUrl === 'null') {
            console.error('❌ PLANTA_CRUDS_API_URL no está configurada correctamente:', PLANTA_CRUDS_API_URL);
            throw new Error('PLANTA_CRUDS_API_URL no está configurada. Verifica la variable de entorno PLANTA_CRUDS_API_URL en .env');
        }
        
        if (loadingDetails) {
            loadingDetails.textContent = 'Enviando petición a plantaCruds...';
        }
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: PEDIDO_ENVIO_IDS }),
            mode: 'cors',
        });
        
        if (loadingDetails) {
            loadingDetails.textContent = 'Procesando respuesta...';
        }
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Error en respuesta HTTP:', response.status, response.statusText);
            console.error('❌ Cuerpo del error:', errorText);
            throw new Error(`Error ${response.status}: ${response.statusText}. ${errorText.substring(0, 200)}`);
        }
        
        const data = await response.json();
        console.log('📦 Respuesta completa:', data);
        console.log('📦 Envíos recibidos:', {
            en_transito: data.en_transito?.length || 0,
            esperando: data.esperando?.length || 0,
            entregados: data.entregados?.length || 0,
            cancelados: data.cancelados?.length || 0,
        });
        
        const enviosFiltrados = {
            en_transito: data.en_transito || [],
            esperando: data.esperando || [],
            entregados: data.entregados || [],
            cancelados: data.cancelados || []
        };
        
        console.log('✅ Envíos filtrados:', enviosFiltrados);
        console.log(`📊 Resumen: ${enviosFiltrados.en_transito.length} en tránsito, ${enviosFiltrados.esperando.length} esperando, ${enviosFiltrados.entregados.length} entregados, ${enviosFiltrados.cancelados.length} cancelados`);
        
        if (loadingMessage) {
            loadingMessage.style.display = 'none';
        }
        
        renderizarListaEnvios(enviosFiltrados.en_transito || [], enviosFiltrados.esperando || [], enviosFiltrados.entregados || [], enviosFiltrados.cancelados || []);
        
        // Llamar a actualizarMapaConEnvios si está definida y el mapa está listo
        if (typeof actualizarMapaConEnvios === 'function' && map && typeof maplibregl !== 'undefined') {
            await actualizarMapaConEnvios(enviosFiltrados.en_transito || []);
        } else {
            console.log('📦 Envíos en tránsito para mostrar en mapa:', enviosFiltrados.en_transito.length);
        }
        
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
        
        if (btnIcon) {
            btnIcon.classList.remove('fa-spin');
            btnIcon.classList.remove('spinner-border');
        }
        
    } catch (error) {
        console.error('❌ Error actualizando envíos:', error);
        console.error('❌ Stack trace:', error.stack);
        
        const estadoElement = document.getElementById('estado-conexion');
        if (estadoElement) {
            estadoElement.className = 'badge bg-danger';
            estadoElement.innerHTML = '<i class="bi bi-exclamation-circle"></i> Error: ' + (error.message || 'Error desconocido');
        }
        
        if (loadingMessage) {
            loadingMessage.style.display = 'none';
        }
        
        if (container) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    <strong>Error al cargar envíos:</strong><br>
                    ${error.message || 'Error desconocido'}<br>
                    <small>Verifica que:</small>
                    <ul class="small">
                        <li>plantaCruds esté corriendo en <code>${PLANTA_CRUDS_API_URL}</code></li>
                        <li>El endpoint <code>/api/rutas/envios-por-ids</code> esté disponible</li>
                        <li>No haya problemas de CORS o firewall</li>
                    </ul>
                    <button class="btn btn-sm btn-primary mt-2" onclick="window.actualizarEnvios()">
                        <i class="bi bi-arrow-clockwise"></i> Reintentar
                    </button>
                </div>
            `;
        }
        
        if (btnIcon) {
            btnIcon.classList.remove('fa-spin');
            btnIcon.classList.remove('spinner-border');
        }
    }
};

// DEFINIR calcularProgreso ANTES de renderizarListaEnvios
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

// DEFINIR renderizarListaEnvios ANTES de actualizarEnvios para que esté disponible
function renderizarListaEnvios(enTransito, esperando, entregados = [], cancelados = []) {
    const container = document.getElementById('lista-envios');
    if (!container) {
        console.error('❌ Container lista-envios no encontrado');
        return;
    }
    
    let html = '';
    const totalEnvios = enTransito.length + esperando.length + entregados.length + cancelados.length;
    
    console.log('🎨 Renderizando lista de envíos:', {
        en_transito: enTransito.length,
        esperando: esperando.length,
        entregados: entregados.length,
        cancelados: cancelados.length,
        total: totalEnvios
    });
    
    if (totalEnvios === 0) {
        html = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                <strong>No hay envíos asociados a tus pedidos</strong><br>
                <small>Los envíos aparecerán aquí cuando se asignen desde plantaCruds y estén en tránsito, asignados, entregados o cancelados.</small>
            </div>
        `;
        container.innerHTML = html;
        return;
    }
    
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
    
    html += `<h6 class="text-danger mt-3"><i class="bi bi-x-circle-fill"></i> Cancelados (${cancelados.length})</h6>`;
    
    if (cancelados.length === 0) {
        html += `<div class="alert alert-secondary py-2"><i class="bi bi-info-circle"></i> No hay envíos cancelados</div>`;
    } else {
        cancelados.forEach(envio => {
            const fechaCancelacion = envio.fecha_cancelacion ? new Date(envio.fecha_cancelacion).toLocaleString('es-ES') : 'N/A';
            html += `
                <div class="envio-card mb-2 p-2 border rounded bg-light" style="opacity: 0.8; border-color: #dc3545 !important;">
                    <span class="badge bg-danger mb-1">❌ CANCELADO</span>
                    <p class="mb-1 mt-1"><strong>${envio.codigo}</strong></p>
                    <p class="mb-1 small text-muted">📦 ${envio.almacen_nombre || 'N/A'}</p>
                    <p class="mb-1 small text-muted">📍 ${envio.direccion_completa || 'N/A'}</p>
                    ${envio.transportista_nombre ? `<p class="mb-1 small text-muted">👤 ${envio.transportista_nombre}</p>` : ''}
                    <small class="text-muted"><i class="bi bi-calendar-x"></i> Cancelado: ${fechaCancelacion}</small>
                </div>
            `;
        });
    }
    
    container.innerHTML = html;
}

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

// Variables para MapLibre
let map = null;
let marcadores = {};

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
                    
                    // Convertir rutaLeaflet a formato [lng, lat] para MapLibre
                    const rutaMapLibre = rutaLeaflet.map(p => [p[1], p[0]]);
                    
                    // Marcador de destino
                    const elDestino = document.createElement('div');
                    elDestino.innerHTML = '📦';
                    elDestino.style.fontSize = '20px';
                    const marcadorDestino = new maplibregl.Marker(elDestino)
                        .setLngLat([ultimoPunto[1], ultimoPunto[0]])
                        .setPopup(new maplibregl.Popup().setHTML(`<b>📦 Destino</b><br>Envío ${envioId}`))
                        .addTo(map);
                    
                    // Marcador de vehículo
                    const elVehiculo = document.createElement('div');
                    elVehiculo.innerHTML = '🚚';
                    elVehiculo.style.fontSize = '24px';
                    const marcadorVehiculo = new maplibregl.Marker(elVehiculo)
                        .setLngLat([primerPunto[1], primerPunto[0]])
                        .setPopup(new maplibregl.Popup().setHTML(`<b>🚚 Envío ${envioId}</b><br>Iniciando ruta...`))
                        .addTo(map);
                    
                    // Ruta completa
                    if (!map.getSource(`ruta-${envioId}`)) {
                        map.addSource(`ruta-${envioId}`, {
                            type: 'geojson',
                            data: {
                                type: 'Feature',
                                properties: {},
                                geometry: {
                                    type: 'LineString',
                                    coordinates: rutaMapLibre
                                }
                            }
                        });
                        map.addLayer({
                            id: `ruta-${envioId}`,
                            type: 'line',
                            source: `ruta-${envioId}`,
                            layout: {
                                'line-join': 'round',
                                'line-cap': 'round'
                            },
                            paint: {
                                'line-color': '#2196F3',
                                'line-width': 5,
                                'line-opacity': 0.5,
                                'line-dasharray': [2, 2]
                            }
                        });
                    }
                    
                    // Ruta recorrida
                    if (!map.getSource(`ruta-rec-${envioId}`)) {
                        map.addSource(`ruta-rec-${envioId}`, {
                            type: 'geojson',
                            data: {
                                type: 'Feature',
                                properties: {},
                                geometry: {
                                    type: 'LineString',
                                    coordinates: [[primerPunto[1], primerPunto[0]]]
                                }
                            }
                        });
                        map.addLayer({
                            id: `ruta-rec-${envioId}`,
                            type: 'line',
                            source: `ruta-rec-${envioId}`,
                            layout: {
                                'line-join': 'round',
                                'line-cap': 'round'
                            },
                            paint: {
                                'line-color': '#4CAF50',
                                'line-width': 6,
                                'line-opacity': 0.9
                            }
                        });
                    }
                    
                    marcadores[envioId] = { 
                        vehiculo: marcadorVehiculo, 
                        destino: marcadorDestino,
                        ruta: `ruta-${envioId}`,
                        rutaRecorrida: `ruta-rec-${envioId}`
                    };
                    
                    // Ajustar vista
                    const bbox = rutaMapLibre.reduce((acc, coord) => {
                        return [
                            [Math.min(acc[0][0], coord[0]), Math.min(acc[0][1], coord[1])],
                            [Math.max(acc[1][0], coord[0]), Math.max(acc[1][1], coord[1])]
                        ];
                    }, [[rutaMapLibre[0][0], rutaMapLibre[0][1]], [rutaMapLibre[0][0], rutaMapLibre[0][1]]]);
                    map.fitBounds(bbox, { padding: 50 });
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
                if (marcadores[envioId].vehiculo) marcadores[envioId].vehiculo.remove();
                if (marcadores[envioId].destino) marcadores[envioId].destino.remove();
                if (marcadores[envioId].ruta) {
                    if (map.getLayer(marcadores[envioId].ruta)) map.removeLayer(marcadores[envioId].ruta);
                    if (map.getSource(marcadores[envioId].ruta)) map.removeSource(marcadores[envioId].ruta);
                }
                if (marcadores[envioId].rutaRecorrida) {
                    if (map.getLayer(marcadores[envioId].rutaRecorrida)) map.removeLayer(marcadores[envioId].rutaRecorrida);
                    if (map.getSource(marcadores[envioId].rutaRecorrida)) map.removeSource(marcadores[envioId].rutaRecorrida);
                }
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
    
    const nuevaPosicion = [lng, lat]; // [lng, lat] para MapLibre
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
        marcadores[envioId].vehiculo.setLngLat(nuevaPosicion);
        if (marcadores[envioId].rutaRecorrida && posicionesWebSocket[envioId].length > 0) {
            const source = map.getSource(marcadores[envioId].rutaRecorrida);
            if (source) {
                source.setData({
                    type: 'Feature',
                    properties: {},
                    geometry: {
                        type: 'LineString',
                        coordinates: posicionesWebSocket[envioId]
                    }
                });
            }
        }
        const popup = marcadores[envioId].vehiculo.getPopup();
        if (popup) {
            popup.setHTML(`<b>🚚 Envío ${envioId}</b><br>Progreso: ${Math.round(progreso * 100)}%<br><small>🔴 En vivo</small>`);
        }
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
    // Evitar inicializaciones múltiples
    if (mapaInicializado && map) {
        return;
    }
    
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        return;
    }
    
    // Verificar que MapLibre esté cargado
    if (typeof maplibregl === 'undefined') {
        setTimeout(() => inicializarMapa(), 200);
        return;
    }
    
    // Si el mapa ya existe, destruirlo
    if (map) {
        try {
            map.remove();
        } catch (e) {}
        map = null;
        mapaInicializado = false;
    }
    
    // Verificar dimensiones
    if (mapElement.offsetWidth < 100 || mapElement.offsetHeight < 100) {
        setTimeout(() => inicializarMapa(), 200);
        return;
    }
    
    // Limpiar contenedor
    mapElement.innerHTML = '';
    
    try {
        console.log('🗺️ Creando mapa MapLibre...');
        
        // Crear mapa con MapLibre GL JS
        map = new maplibregl.Map({
            container: 'map',
            style: {
                version: 8,
                sources: {
                    'osm-tiles': {
                        type: 'raster',
                        tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                        tileSize: 256,
                        attribution: '© OpenStreetMap contributors'
                    }
                },
                layers: [{
                    id: 'osm-tiles-layer',
                    type: 'raster',
                    source: 'osm-tiles',
                    minzoom: 0,
                    maxzoom: 19
                }]
            },
            center: [PLANTA_COORDS[1], PLANTA_COORDS[0]], // [lng, lat]
            zoom: 13
        });
        
        // Agregar controles de navegación
        map.addControl(new maplibregl.NavigationControl());
        
        // Esperar a que el mapa esté cargado
        map.on('load', () => {
            console.log('✅ Mapa cargado, agregando marcador...');
            
            // Agregar marcador de la planta
            const el = document.createElement('div');
            el.className = 'marker-planta';
            el.style.width = '30px';
            el.style.height = '30px';
            el.style.borderRadius = '50%';
            el.style.backgroundColor = '#dc3545';
            el.style.border = '3px solid white';
            el.style.boxShadow = '0 2px 6px rgba(0,0,0,0.3)';
            el.style.display = 'flex';
            el.style.alignItems = 'center';
            el.style.justifyContent = 'center';
            el.innerHTML = '<span style="color: white; font-size: 16px;">🏭</span>';
            
            new maplibregl.Marker(el)
                .setLngLat([PLANTA_COORDS[1], PLANTA_COORDS[0]])
                .setPopup(new maplibregl.Popup().setHTML('<b>🏭 Planta - Origen</b><br>Santa Cruz de la Sierra'))
                .addTo(map);
            
            mapaInicializado = true;
            console.log('✅ Mapa MapLibre inicializado correctamente');
        });
        
        map.on('error', (e) => {
            console.error('❌ Error en el mapa:', e);
        });
        
        // Resize handler
        if (!window.mapResizeHandler) {
            window.mapResizeHandler = () => {
                if (map) {
                    setTimeout(() => map.resize(), 100);
                }
            };
            window.addEventListener('resize', window.mapResizeHandler);
        }
    } catch (error) {
        console.error('❌ Error creando mapa:', error);
        mapaInicializado = false;
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
    
    // Inicializar mapa cuando todo esté listo
    const initMapWhenReady = () => {
        if (typeof maplibregl === 'undefined') {
            console.log('⏳ Esperando MapLibre...');
            setTimeout(initMapWhenReady, 200);
            return;
        }
        
        if (document.readyState === 'complete') {
            setTimeout(() => {
                console.log('🗺️ Inicializando mapa...');
                inicializarMapa();
            }, 500);
        } else {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    console.log('🗺️ Inicializando mapa...');
                    inicializarMapa();
                }, 500);
            });
        }
    };
    
    initMapWhenReady();
    
    try {
        inicializarWebSocket();
        console.log('✅ WebSocket inicializado');
    } catch (error) {
        console.error('❌ Error inicializando WebSocket:', error);
    }
    
    // Llamar inmediatamente a actualizarEnvios
    console.log('🔄 Intentando actualizar envíos...');
    console.log('📋 PEDIDO_ENVIO_IDS:', PEDIDO_ENVIO_IDS);
    console.log('📋 Tipo:', typeof PEDIDO_ENVIO_IDS);
    console.log('📋 Es array:', Array.isArray(PEDIDO_ENVIO_IDS));
    console.log('📋 Longitud:', PEDIDO_ENVIO_IDS?.length);
    console.log('🔗 PLANTA_CRUDS_API_URL:', PLANTA_CRUDS_API_URL);
    
    // Actualizar mensaje de carga
    const loadingDetails = document.getElementById('loading-details');
    if (loadingDetails) {
        const count = PEDIDO_ENVIO_IDS?.length || 0;
        loadingDetails.textContent = count > 0 
            ? `Encontrados ${count} envío(s) asociado(s) - Consultando API...`
            : 'No hay envíos asociados a tus pedidos';
    }
    
    // Llamar directamente a actualizarEnvios (ya está definida antes de DOMContentLoaded)
    console.log('🔍 Verificando función actualizarEnvios...');
    console.log('🔍 typeof window.actualizarEnvios:', typeof window.actualizarEnvios);
    
    // Forzar ejecución después de un pequeño delay para asegurar que todo esté listo
    setTimeout(() => {
        if (typeof window.actualizarEnvios === 'function') {
            console.log('✅ Llamando a actualizarEnvios...');
            if (loadingDetails) {
                loadingDetails.textContent = 'Consultando API de plantaCruds...';
            }
            try {
                window.actualizarEnvios();
            } catch (error) {
                console.error('❌ Error ejecutando actualizarEnvios:', error);
                const container = document.getElementById('lista-envios');
                const loadingMessage = document.getElementById('loading-message');
                if (container) {
                    if (loadingMessage) loadingMessage.style.display = 'none';
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong>Error ejecutando actualizarEnvios:</strong><br>
                            ${error.message || 'Error desconocido'}<br>
                            <button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Recargar página
                            </button>
                        </div>
                    `;
                }
            }
        } else {
            console.error('❌ actualizarEnvios no está definida');
            const container = document.getElementById('lista-envios');
            const loadingMessage = document.getElementById('loading-message');
            if (container) {
                if (loadingMessage) loadingMessage.style.display = 'none';
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Error:</strong> La función actualizarEnvios no está definida. Recarga la página.
                        <br><button class="btn btn-sm btn-primary mt-2" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Recargar página
                        </button>
                    </div>
                `;
            }
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

// Las funciones actualizarEnvios y renderizarListaEnvios ya están definidas arriba, antes de DOMContentLoaded

let actualizandoMapa = false;

async function actualizarMapaConEnvios(enviosEnTransito) {
    if (actualizandoMapa || !map || typeof maplibregl === 'undefined') return;
    actualizandoMapa = true;
    
    try {
        // Limpiar marcadores anteriores
        Object.keys(marcadores).forEach(envioId => {
            if (marcadores[envioId]) {
                if (marcadores[envioId].vehiculo) marcadores[envioId].vehiculo.remove();
                if (marcadores[envioId].destino) marcadores[envioId].destino.remove();
                if (marcadores[envioId].ruta) {
                    if (map.getLayer(`ruta-${envioId}`)) map.removeLayer(`ruta-${envioId}`);
                    if (map.getSource(`ruta-${envioId}`)) map.removeSource(`ruta-${envioId}`);
                }
                if (marcadores[envioId].rutaRecorrida) {
                    if (map.getLayer(`ruta-rec-${envioId}`)) map.removeLayer(`ruta-rec-${envioId}`);
                    if (map.getSource(`ruta-rec-${envioId}`)) map.removeSource(`ruta-rec-${envioId}`);
                }
            }
        });
        marcadores = {};
        
        const bounds = [[PLANTA_COORDS[1], PLANTA_COORDS[0]]];
        
        for (const envio of enviosEnTransito) {
            const envioId = envio.id;
            
            // FILTRAR: Solo procesar envíos de nuestros pedidos
            if (!PEDIDO_ENVIO_IDS.includes(envioId)) {
                continue;
            }
            
            const destinoLat = parseFloat(envio.destino_lat) || -17.78;
            const destinoLng = parseFloat(envio.destino_lng) || -63.18;
            const destino = [destinoLng, destinoLat]; // [lng, lat] para MapLibre
            
            // Marcador de destino
            const elDestino = document.createElement('div');
            elDestino.className = 'marker-destino';
            elDestino.style.width = '25px';
            elDestino.style.height = '25px';
            elDestino.style.borderRadius = '50%';
            elDestino.style.backgroundColor = '#28a745';
            elDestino.style.border = '3px solid white';
            elDestino.style.boxShadow = '0 2px 6px rgba(0,0,0,0.3)';
            elDestino.innerHTML = '📦';
            
            const marcadorDestino = new maplibregl.Marker(elDestino)
                .setLngLat(destino)
                .setPopup(new maplibregl.Popup().setHTML(`<b>📦 ${envio.almacen_nombre}</b><br>${envio.direccion_completa || 'Destino del envío'}`))
                .addTo(map);
            
            bounds.push(destino);
            
            marcadores[envioId] = { 
                vehiculo: null, 
                destino: marcadorDestino,
                ruta: null,
                rutaRecorrida: null
            };
        }
        
        // Ajustar el zoom para mostrar todos los marcadores
        if (bounds.length > 1) {
            const bbox = bounds.reduce((acc, coord) => {
                return [
                    [Math.min(acc[0][0], coord[0]), Math.min(acc[0][1], coord[1])],
                    [Math.max(acc[1][0], coord[0]), Math.max(acc[1][1], coord[1])]
                ];
            }, [[bounds[0][0], bounds[0][1]], [bounds[0][0], bounds[0][1]]]);
            
            map.fitBounds(bbox, { padding: 50 });
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
    if (!map || typeof maplibregl === 'undefined') return;
    
    envioSeleccionado = id;
    const destino = [parseFloat(lng), parseFloat(lat)]; // [lng, lat] para MapLibre
    
    // Centrar el mapa en el destino
    map.flyTo({ center: destino, zoom: 14 });
    
    // Abrir popup del marcador de destino si existe
    if (marcadores[id] && marcadores[id].destino) {
        marcadores[id].destino.togglePopup();
    }
    
    document.getElementById('control-panel').style.display = 'block';
    document.getElementById('envio-codigo').textContent = codigo;
    document.getElementById('envio-estado').textContent = 'EN TRÁNSITO';
    document.getElementById('envio-estado').className = 'badge bg-info';
    document.getElementById('info-panel').innerHTML = 
        `<i class="bi bi-truck"></i> Siguiendo envío <strong>${codigo}</strong> en tiempo real`;
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
    if (map && typeof maplibregl !== 'undefined') {
        map.flyTo({ center: [PLANTA_COORDS[1], PLANTA_COORDS[0]], zoom: 13 });
    }
};

window.addEventListener('beforeunload', function() {
    if (intervaloActualizacion) clearInterval(intervaloActualizacion);
    if (socket) socket.disconnect();
});
</script>
@endpush
