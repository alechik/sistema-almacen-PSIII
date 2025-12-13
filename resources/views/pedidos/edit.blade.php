@extends('dashboard-layouts.header-footer')
@section('content')
@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Editar Pedido</h3>
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pedidos.index') }}">Pedidos</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('pedidos.update', $pedido->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="app-content">
            <div class="container-fluid">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- ========================= CABECERA ========================= -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Datos del Pedido</h5>
                    </div>

                    <div class="card-body">

                        <div class="row">
                            <!-- código -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código Comprobante *</label>
                                <input type="text" class="form-control" disabled
                                    value="{{ $pedido->codigo_comprobante }}">
                            </div>

                            <!-- FECHA DE ENTREGA DESEADA -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha de Entrega Deseada *</label>
                                <input type="date" name="fecha" class="form-control"
                                    value="{{ old('fecha', $pedido->fecha->format('Y-m-d')) }}" 
                                    min="{{ now()->format('Y-m-d') }}"
                                    required>
                                <small class="text-muted">Seleccione la fecha en que desea recibir el pedido en el almacén</small>
                            </div>

                            <!-- almacén -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Almacén *</label>
                                <select name="almacen_id" id="almacen_id" class="form-select" required onchange="mostrarUbicacionAlmacen(this)">
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $a)
                                        <option value="{{ $a->id }}"
                                            data-nombre="{{ $a->nombre }}"
                                            data-latitud="{{ $a->latitud }}"
                                            data-longitud="{{ $a->longitud }}"
                                            data-ubicacion="{{ $a->ubicacion }}"
                                            {{ $pedido->almacen_id == $a->id ? 'selected' : '' }}>
                                            {{ $a->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- MAPA DE UBICACIÓN DEL ALMACÉN -->
                            <div class="col-md-12 mb-3" id="mapa_almacen_container" style="display: none;">
                                <label class="form-label fw-bold">Ubicación del Almacén Seleccionado</label>
                                <div id="mapa_almacen" style="height: 400px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
                                <small class="text-muted d-block mt-2">
                                    <i class="bi bi-info-circle"></i> 
                                    Esta es la ubicación del almacén seleccionado. Esta información se enviará a Trazabilidad y luego a PlantaCruds para el envío.
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- PROVEEDOR (FIJO: PLANTA) -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Proveedor</label>
                                <input type="hidden" name="proveedor_id" value="1">
                                <input type="text" class="form-control" value="Planta" readonly>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ========================= DETALLE ========================= -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <h5 class="mb-0">Detalle del Pedido</h5>
                        <button type="button" id="addRow" class="btn btn-success btn-sm">
                            + Agregar Producto
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0" id="detalleTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th style="width:150px;">Cantidad</th>
                                    <th style="width:120px;">Acción</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $index = 0; @endphp
                                @foreach($pedido->detalles as $d)
                                <tr>
                                    <td>
                                        <select name="productos[{{ $index }}][producto_id]"
                                                class="form-select producto-select" required>
                                            <option value="">Seleccione producto...</option>
                                            @if(isset($productosTrazabilidad))
                                                @foreach($productosTrazabilidad as $prod)
                                                    <option value="{{ $prod->producto_id ?? $prod->id }}"
                                                        data-nombre="{{ $prod->nombre }}"
                                                        {{ ($d->producto_trazabilidad_id == ($prod->producto_id ?? $prod->id)) || ($d->producto_id == ($prod->producto_id ?? $prod->id)) ? 'selected' : '' }}>
                                                        {{ $prod->nombre }} {{ $prod->codigo ? '(' . $prod->codigo . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <input type="hidden" name="productos[{{ $index }}][producto_nombre]" 
                                               class="producto-nombre-input" 
                                               value="{{ $d->producto_nombre ?? ($d->producto->nombre ?? '') }}">
                                    </td>

                                    <td>
                                        <input type="number" min="0.01" step="0.01"
                                            name="productos[{{ $index }}][cantidad]"
                                            class="form-control"
                                            value="{{ $d->cantidad }}" required>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btnDelete">Eliminar</button>
                                    </td>
                                </tr>
                                @php $index++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer text-end">
                        <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Pedido</button>
                    </div>
                </div>

            </div>
        </div>
    </form>

</main>

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let index = {{ $index ?? 0 }};
const productosTrazabilidad = @json($productosTrazabilidad ?? []);
let mapaAlmacen = null;
let marcadorAlmacen = null;

// Función para mostrar la ubicación del almacén en el mapa
function mostrarUbicacionAlmacen(select) {
    const selectedOption = select.options[select.selectedIndex];
    const container = document.getElementById('mapa_almacen_container');
    const mapaDiv = document.getElementById('mapa_almacen');
    
    if (select.value && selectedOption.getAttribute('data-latitud') && selectedOption.getAttribute('data-longitud')) {
        const latitud = parseFloat(selectedOption.getAttribute('data-latitud'));
        const longitud = parseFloat(selectedOption.getAttribute('data-longitud'));
        const nombre = selectedOption.getAttribute('data-nombre');
        const ubicacion = selectedOption.getAttribute('data-ubicacion');
        
        // Mostrar el contenedor del mapa
        container.style.display = 'block';
        
        // Inicializar o actualizar el mapa
        if (!mapaAlmacen) {
            mapaAlmacen = L.map('mapa_almacen').setView([latitud, longitud], 15);
            
            // Agregar capa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(mapaAlmacen);
        } else {
            mapaAlmacen.setView([latitud, longitud], 15);
        }
        
        // Remover marcador anterior si existe
        if (marcadorAlmacen) {
            mapaAlmacen.removeLayer(marcadorAlmacen);
        }
        
        // Agregar nuevo marcador
        marcadorAlmacen = L.marker([latitud, longitud]).addTo(mapaAlmacen);
        
        // Agregar popup con información del almacén
        const popupContent = `
            <strong>${nombre}</strong><br>
            ${ubicacion ? `Dirección: ${ubicacion}<br>` : ''}
            Coordenadas: ${latitud.toFixed(6)}, ${longitud.toFixed(6)}
        `;
        marcadorAlmacen.bindPopup(popupContent).openPopup();
        
        // Asegurar que el mapa se renderice correctamente
        setTimeout(() => {
            if (mapaAlmacen) {
                mapaAlmacen.invalidateSize();
            }
        }, 100);
    } else {
        // Ocultar el mapa si no hay almacén seleccionado o no tiene coordenadas
        container.style.display = 'none';
        if (marcadorAlmacen) {
            mapaAlmacen.removeLayer(marcadorAlmacen);
            marcadorAlmacen = null;
        }
    }
}

// Ejecutar al cargar la página si hay un almacén preseleccionado
document.addEventListener('DOMContentLoaded', () => {
    const almacenSelect = document.getElementById('almacen_id');
    if (almacenSelect && almacenSelect.value) {
        mostrarUbicacionAlmacen(almacenSelect);
    }
});

/* ============================
   CARGAR PRODUCTOS DESDE TRAZABILIDAD
   ============================ */
function cargarProductosTrazabilidad() {
    const trazabilidadUrl = '{{ env("TRAZABILIDAD_API_URL", "http://localhost:8000/api") }}';
    
    fetch(`${trazabilidadUrl}/products`)
        .then(r => r.json())
        .then(data => {
            const productos = data.data || data || [];
            
            // Actualizar todos los selects de productos
            document.querySelectorAll('.producto-select').forEach(sel => {
                const currentValue = sel.value;
                sel.innerHTML = '<option value="">Seleccione producto...</option>';
                
                productos.forEach(p => {
                    const productoId = p.producto_id || p.id;
                    const selected = currentValue == productoId ? 'selected' : '';
                    sel.innerHTML += `<option value="${productoId}" data-nombre="${p.nombre}" ${selected}>${p.nombre} ${p.codigo ? '(' + p.codigo + ')' : ''}</option>`;
                });
            });
        })
        .catch(error => {
            console.error('Error al cargar productos desde Trazabilidad:', error);
            // Usar productos cargados desde el servidor si hay error
            if (productosTrazabilidad.length > 0) {
                document.querySelectorAll('.producto-select').forEach(sel => {
                    const currentValue = sel.value;
                    sel.innerHTML = '<option value="">Seleccione producto...</option>';
                    productosTrazabilidad.forEach(p => {
                        const productoId = p.producto_id || p.id;
                        const selected = currentValue == productoId ? 'selected' : '';
                        sel.innerHTML += `<option value="${productoId}" data-nombre="${p.nombre}">${p.nombre} ${p.codigo ? '(' + p.codigo + ')' : ''}</option>`;
                    });
                });
            }
        });
}

// Cargar productos al iniciar
document.addEventListener('DOMContentLoaded', () => {
    cargarProductosTrazabilidad();
    
    // Actualizar nombres de productos existentes
    document.querySelectorAll('.producto-select').forEach(sel => {
        sel.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const nombre = selectedOption.getAttribute('data-nombre') || '';
            const row = this.closest('tr');
            const nombreInput = row.querySelector('.producto-nombre-input');
            if (nombreInput) {
                nombreInput.value = nombre;
            }
        });
    });
});

/* ============================
   AGREGAR FILA
   ============================ */
document.getElementById('addRow').addEventListener('click', () => {

    const tbody = document.querySelector('#detalleTable tbody');

    let row = `
        <tr>
            <td>
                <select name="productos[${index}][producto_id]"
                        class="form-select producto-select" required>
                    <option value="">Seleccione producto...</option>
                    ${productosTrazabilidad.map(p => {
                        const productoId = p.producto_id || p.id;
                        return `<option value="${productoId}" data-nombre="${p.nombre}">${p.nombre} ${p.codigo ? '(' + p.codigo + ')' : ''}</option>`;
                    }).join('')}
                </select>
                <input type="hidden" name="productos[${index}][producto_nombre]" class="producto-nombre-input">
            </td>

            <td>
                <input type="number" min="0.01" step="0.01"
                       name="productos[${index}][cantidad]"
                       class="form-control" required>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btnDelete">Eliminar</button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', row);
    index++;

    // Actualizar nombre del producto cuando se selecciona
    const newSelect = tbody.querySelector('tr:last-child .producto-select');
    const newNombreInput = tbody.querySelector('tr:last-child .producto-nombre-input');
    
    newSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const nombre = selectedOption.getAttribute('data-nombre') || '';
        newNombreInput.value = nombre;
    });
    
    // Si hay productos cargados desde Trazabilidad, recargar
    if (productosTrazabilidad.length === 0) {
        cargarProductosTrazabilidad();
    }
});

// Actualizar nombre del producto cuando cambia la selección
document.addEventListener('change', (e) => {
    if (e.target.classList.contains('producto-select')) {
        const selectedOption = e.target.options[e.target.selectedIndex];
        const nombre = selectedOption.getAttribute('data-nombre') || '';
        const row = e.target.closest('tr');
        const nombreInput = row.querySelector('.producto-nombre-input');
        if (nombreInput) {
            nombreInput.value = nombre;
        }
    }
});

/* ============================
   ELIMINAR FILA
   ============================ */
document.addEventListener('click', e => {
    if (e.target.classList.contains('btnDelete')) {
        e.target.closest('tr').remove();
    }
});
</script>
@endpush

@endsection
