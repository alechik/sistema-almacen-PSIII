@extends('dashboard-layouts.header-footer')
@section('content')

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

                            <!-- fecha -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha *</label>
                                <input type="date" name="fecha" class="form-control"
                                    value="{{ old('fecha', $pedido->fecha) }}" required>
                            </div>

                            <!-- fecha min -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Mínima *</label>
                                <input type="date" name="fecha_min" class="form-control"
                                    value="{{ old('fecha_min', $pedido->fecha_min) }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <!-- fecha max -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Máxima *</label>
                                <input type="date" name="fecha_max" class="form-control"
                                    value="{{ old('fecha_max', $pedido->fecha_max) }}" required>
                            </div>

                            <!-- almacén -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Almacén *</label>
                                <select name="almacen_id" id="almacen_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $a)
                                        <option value="{{ $a->id }}"
                                            {{ $pedido->almacen_id == $a->id ? 'selected' : '' }}>
                                            {{ $a->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- proveedor -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Proveedor *</label>
                                <select name="proveedor_id" id="proveedor_id" class="form-select" required>
                                    @foreach($proveedores as $p)
                                        <option value="{{ $p['id'] }}"
                                            {{ $pedido->proveedor_id == $p['id'] ? 'selected' : '' }}>
                                            {{ $p['nombre'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- operador -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Operador *</label>
                                <select name="operador_id" id="operador_id" class="form-select" required>
                                    @foreach($operadores as $o)
                                        <option value="{{ $o->id }}"
                                            {{ $pedido->operador_id == $o->id ? 'selected' : '' }}>
                                            {{ $o->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- transportista -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Transportista *</label>
                                <select name="transportista_id" id="transportista_id" class="form-select" required>
                                    @foreach($transportistas as $t)
                                        <option value="{{ $t->id }}"
                                            {{ $pedido->transportista_id == $t->id ? 'selected' : '' }}>
                                            {{ $t->full_name }}
                                        </option>
                                    @endforeach
                                </select>
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
                                            @foreach($productos as $prod)
                                                <option value="{{ $prod->id }}"
                                                    {{ $d->producto_id == $prod->id ? 'selected' : '' }}>
                                                    {{ $prod->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number" min="1"
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
<script>
let index = {{ $index ?? 0 }};

/* =======================
   CAMBIO DE ALMACÉN
======================= */
document.getElementById('almacen_id').addEventListener('change', function () {
    let almacenId = this.value;

    fetch(`/ajax/almacen/${almacenId}/usuarios`)
        .then(r => r.json())
        .then(data => {

            let opSel = document.getElementById('operador_id');
            let trSel = document.getElementById('transportista_id');

            opSel.innerHTML = '<option value="">Seleccione...</option>';
            trSel.innerHTML = '<option value="">Seleccione...</option>';

            data.operadores.forEach(u => {
                opSel.innerHTML += `<option value="${u.id}">${u.full_name}</option>`;
            });

            data.transportistas.forEach(u => {
                trSel.innerHTML += `<option value="${u.id}">${u.full_name}</option>`;
            });
        });
});

/* =======================
   CAMBIO DE PROVEEDOR:
   CARGA PRODUCTOS DINÁMICAMENTE
======================= */
document.getElementById('proveedor_id').addEventListener('change', function () {

    let proveedorId = this.value;

    fetch(`/ajax/proveedor/${proveedorId}/productos`)
        .then(r => r.json())
        .then(productos => {

            document.querySelectorAll('.producto-select').forEach(sel => {

                let html = '<option value="">Seleccione producto...</option>';

                productos.forEach(p => {
                    html += `<option value="${p.id}">${p.nombre}</option>`;
                });

                sel.innerHTML = html;
            });
        });

});

/* =======================
   AGREGAR FILA
======================= */
document.getElementById('addRow').addEventListener('click', function () {

    const tbody = document.querySelector('#detalleTable tbody');

    let row = `
        <tr>
            <td>
                <select name="productos[${index}][producto_id]" class="form-select producto-select" required>
                    <option value="">Seleccione producto...</option>
                </select>
            </td>

            <td>
                <input type="number" min="1" name="productos[${index}][cantidad]"
                       class="form-control" required>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btnDelete">Eliminar</button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', row);
    index++;

    // cargar productos según proveedor actual
    document.getElementById('proveedor_id').dispatchEvent(new Event('change'));

});

/* =======================
   ELIMINAR FILA
======================= */
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('btnDelete')) {
        e.target.closest('tr').remove();
    }
});
</script>
@endpush

@endsection
