@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-plus-circle text-success"></i> Nuevo Pedido a Planta
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('envios-planta.index') }}">Envíos Planta</a></li>
                        <li class="breadcrumb-item active">Nuevo Pedido</li>
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

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    {{ session('warning') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Estado de conexión -->
            <div class="alert {{ $plantaConectada ? 'alert-success' : 'alert-warning' }} mb-4">
                <i class="bi {{ $plantaConectada ? 'bi-check-circle' : 'bi-exclamation-triangle' }}"></i>
                <strong>Estado de Planta:</strong>
                {{ $plantaConectada ? 'Conectado - Pedidos se enviarán directamente' : 'Sin conexión - Pedidos se guardarán localmente' }}
            </div>

            <form action="{{ route('envios-planta.pedido.store') }}" method="POST" id="formPedido">
                @csrf

                <div class="row">
                    <!-- Datos del Pedido -->
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Pedido</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Almacén de Destino <span class="text-danger">*</span></label>
                                        <select name="almacen_id" class="form-select @error('almacen_id') is-invalid @enderror" required>
                                            <option value="">Seleccione un almacén</option>
                                            @foreach($almacenes as $almacen)
                                                <option value="{{ $almacen->id }}" {{ old('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                                    {{ $almacen->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('almacen_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Fecha Requerida <span class="text-danger">*</span></label>
                                        <input type="date" name="fecha_requerida" 
                                               class="form-control @error('fecha_requerida') is-invalid @enderror"
                                               value="{{ old('fecha_requerida', date('Y-m-d', strtotime('+1 day'))) }}" 
                                               min="{{ date('Y-m-d') }}" required>
                                        @error('fecha_requerida')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Hora Aproximada</label>
                                        <input type="time" name="hora_requerida" 
                                               class="form-control"
                                               value="{{ old('hora_requerida', '09:00') }}">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="2" 
                                              placeholder="Instrucciones especiales de entrega...">{{ old('observaciones') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Productos -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-box"></i> Productos a Solicitar</h5>
                                <button type="button" class="btn btn-light btn-sm" id="btnAgregarProducto">
                                    <i class="bi bi-plus"></i> Agregar Producto
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="productosContainer">
                                    <!-- Producto Template -->
                                    <div class="producto-row mb-3 p-3 border rounded" data-index="0">
                                        <div class="row align-items-end">
                                            <div class="col-md-4 mb-2">
                                                <label class="form-label">Producto <span class="text-danger">*</span></label>
                                                <input type="text" name="productos[0][nombre]" 
                                                       class="form-control" placeholder="Nombre del producto" 
                                                       list="listaProductos" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                                                <input type="number" name="productos[0][cantidad]" 
                                                       class="form-control cantidad-input" min="1" value="1" required>
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Peso Unit. (kg)</label>
                                                <input type="number" name="productos[0][peso_unitario]" 
                                                       class="form-control peso-input" step="0.01" min="0" value="0">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <label class="form-label">Precio Unit. (Bs)</label>
                                                <input type="number" name="productos[0][precio_unitario]" 
                                                       class="form-control precio-input" step="0.01" min="0" value="0">
                                            </div>
                                            <div class="col-md-2 mb-2">
                                                <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto w-100" disabled>
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Datalist de productos existentes -->
                                <datalist id="listaProductos">
                                    @foreach($productos as $producto)
                                        <option value="{{ $producto->nombre }}">
                                    @endforeach
                                </datalist>

                                @error('productos')
                                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="col-lg-4">
                        <div class="card mb-4 sticky-top" style="top: 20px;">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="bi bi-calculator"></i> Resumen del Pedido</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush mb-3">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Productos:</span>
                                        <strong id="resumenProductos">1</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Cantidad Total:</span>
                                        <strong id="resumenCantidad">1</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Peso Total:</span>
                                        <strong id="resumenPeso">0.00 kg</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between bg-light">
                                        <span class="fs-5">Total Estimado:</span>
                                        <strong class="fs-5 text-success" id="resumenTotal">Bs. 0.00</strong>
                                    </li>
                                </ul>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-send"></i> Enviar Pedido a Planta
                                    </button>
                                    <a href="{{ route('envios-planta.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-lg"></i> Cancelar
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="card">
                            <div class="card-body">
                                <h6 class="text-muted"><i class="bi bi-info-circle"></i> ¿Cómo funciona?</h6>
                                <ol class="small text-muted mb-0">
                                    <li>Complete los datos del pedido</li>
                                    <li>Agregue los productos que necesita</li>
                                    <li>Envíe el pedido a Planta</li>
                                    <li>Planta procesará y asignará un transportista</li>
                                    <li>Podrá monitorear el envío en tiempo real</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>

</main>

@endsection

@push('scripts')
<script>
    let productoIndex = 1;

    // Agregar nuevo producto
    document.getElementById('btnAgregarProducto').addEventListener('click', function() {
        const container = document.getElementById('productosContainer');
        const template = `
            <div class="producto-row mb-3 p-3 border rounded" data-index="${productoIndex}">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Producto <span class="text-danger">*</span></label>
                        <input type="text" name="productos[${productoIndex}][nombre]" 
                               class="form-control" placeholder="Nombre del producto" 
                               list="listaProductos" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" name="productos[${productoIndex}][cantidad]" 
                               class="form-control cantidad-input" min="1" value="1" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Peso Unit. (kg)</label>
                        <input type="number" name="productos[${productoIndex}][peso_unitario]" 
                               class="form-control peso-input" step="0.01" min="0" value="0">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label">Precio Unit. (Bs)</label>
                        <input type="number" name="productos[${productoIndex}][precio_unitario]" 
                               class="form-control precio-input" step="0.01" min="0" value="0">
                    </div>
                    <div class="col-md-2 mb-2">
                        <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto w-100">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', template);
        productoIndex++;
        actualizarBotonesEliminar();
        actualizarResumen();
    });

    // Eliminar producto
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-eliminar-producto')) {
            e.target.closest('.producto-row').remove();
            actualizarBotonesEliminar();
            actualizarResumen();
        }
    });

    // Actualizar estado de botones eliminar
    function actualizarBotonesEliminar() {
        const rows = document.querySelectorAll('.producto-row');
        rows.forEach((row, index) => {
            const btn = row.querySelector('.btn-eliminar-producto');
            btn.disabled = rows.length === 1;
        });
    }

    // Actualizar resumen
    function actualizarResumen() {
        const rows = document.querySelectorAll('.producto-row');
        let totalProductos = rows.length;
        let totalCantidad = 0;
        let totalPeso = 0;
        let totalPrecio = 0;

        rows.forEach(row => {
            const cantidad = parseInt(row.querySelector('.cantidad-input')?.value) || 0;
            const peso = parseFloat(row.querySelector('.peso-input')?.value) || 0;
            const precio = parseFloat(row.querySelector('.precio-input')?.value) || 0;

            totalCantidad += cantidad;
            totalPeso += cantidad * peso;
            totalPrecio += cantidad * precio;
        });

        document.getElementById('resumenProductos').textContent = totalProductos;
        document.getElementById('resumenCantidad').textContent = totalCantidad;
        document.getElementById('resumenPeso').textContent = totalPeso.toFixed(2) + ' kg';
        document.getElementById('resumenTotal').textContent = 'Bs. ' + totalPrecio.toFixed(2);
    }

    // Escuchar cambios en inputs
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('cantidad-input') || 
            e.target.classList.contains('peso-input') || 
            e.target.classList.contains('precio-input')) {
            actualizarResumen();
        }
    });

    // Inicializar
    actualizarResumen();
</script>
@endpush

