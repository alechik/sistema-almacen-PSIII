@extends('dashboard-layouts.header-footer')
@section('content')

<main class="app-main">

    <!-- HEADER -->
    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Registrar Pedido</h3>

            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pedidos.index') }}">Pedidos</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <form action="{{ route('pedidos.store') }}" method="POST">
        @csrf

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

                <!-- ============================== -->
                <!--  DATOS DEL PEDIDO              -->
                <!-- ============================== -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Datos del Pedido</h5>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <!-- CÓDIGO -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código Comprobante *</label>

                                <input type="text" name="codigo_comprobante" class="form-control"
                                    value="{{ 'P'.(Auth::user()->user_id ?? Auth::user()->id) * 1000000 + (($lastId ?? 0) + 1) }}"
                                    readonly>
                            </div>

                            <!-- FECHA -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha *</label>
                                <input type="date" name="fecha" class="form-control"
                                    value="{{ old('fecha', now()->format('Y-m-d')) }}">
                            </div>

                            <!-- FECHA MIN -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Mínima *</label>
                                <input type="date" name="fecha_min" class="form-control"
                                    value="{{ old('fecha_min', now()->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="row">

                            <!-- FECHA MAX -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Máxima *</label>
                                <input type="date" name="fecha_max" class="form-control"
                                    value="{{ old('fecha_max', now()->addDays(7)->format('Y-m-d')) }}">
                            </div>

                            <!-- ALMACEN -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Almacén *</label>
                                <select name="almacen_id" id="almacen_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $a)
                                        <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">

                            <!-- PROVEEDOR -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Proveedor *</label>
                                <select name="proveedor_id" id="proveedor_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($proveedores as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- OPERADOR -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Operador *</label>
                                <select name="operador_id" id="operador_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>

                            <!-- TRANSPORTISTA -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Transportista *</label>
                                <select name="transportista_id" id="transportista_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                        </div>

                    </div>
                </div>


                <!-- ============================== -->
                <!-- DETALLE DEL PEDIDO            -->
                <!-- ============================== -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
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
                                    <th style="width:150px">Cantidad</th>
                                    <th style="width:120px">Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="card-footer text-end">
                        <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Pedido</button>
                    </div>
                </div>

            </div>
        </div>
    </form>

</main>

@push('scripts')
<script>
let index = 0;

/* ============================
   CAMBIO DE ALMACÉN
   ============================ */
document.getElementById('almacen_id').addEventListener('change', function () {

    let id = this.value;
    if (!id) return;

    fetch(`/ajax/almacen/${id}/usuarios`)
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

/* ============================
   CAMBIO DE PROVEEDOR
   ============================ */
document.getElementById('proveedor_id').addEventListener('change', function () {

    let id = this.value;
    if (!id) return;

    fetch(`/ajax/proveedor/${id}/productos`)
        .then(r => r.json())
        .then(data => {

            document.querySelectorAll('.producto-select').forEach(sel => {
                sel.innerHTML = '<option value="">Seleccione producto...</option>';
                data.forEach(p => sel.innerHTML += `<option value="${p.id}">${p.nombre}</option>`);
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
                </select>
            </td>

            <td>
                <input type="number" min="1"
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

    // Recargar productos según proveedor actual
    document.getElementById('proveedor_id').dispatchEvent(new Event('change'));
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
