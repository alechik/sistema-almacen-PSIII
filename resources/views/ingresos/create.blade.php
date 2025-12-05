@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Registrar Ingreso</h3>
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ingresos.index') }}">Ingresos</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </div>

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
    </div>

    <form action="{{ route('ingresos.store') }}" method="POST">
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

                <!-- SELECCIONAR PEDIDO -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between">
                        <h5 class="mb-0">Seleccionar Pedido Confirmado</h5>
                        <button type="button" id="loadPedido" class="btn btn-light btn-sm">
                            Traer Información
                        </button>
                    </div>

                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Pedido (Estado TERMINADO 3)</label>
                                <select id="pedidoSelect" name="pedido_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($pedidos as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->codigo_comprobante }} — {{ $p->almacen->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- DATOS DEL INGRESO -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Datos del Ingreso</h5>
                    </div>

                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código Comprobante</label>
                                <input type="text" name="codigo_comprobante" class="form-control"
                                    value="{{ (Auth::user()->user_id ?? Auth::user()->id) * 1000000 + (($lastId ?? 0) + 1) }}"
                                    readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha</label>
                                <input type="date" name="fecha" value="{{ now()->format('Y-m-d') }}" class="form-control">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Mínima</label>
                                <input type="date" name="fecha_min" value="{{ now()->format('Y-m-d') }}" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Máxima</label>
                                <input type="date" name="fecha_max" value="{{ now()->addDays(7)->format('Y-m-d') }}" class="form-control">
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Almacén</label>
                                <select name="almacen_id" id="almacen_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $a)
                                        <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Proveedor</label>
                                <!-- Muestra el nombre -->
                                <input type="text" id="proveedor_nombre" class="form-control" readonly>
                                <!-- ID real -->
                                <input type="hidden" id="proveedor_id" name="proveedor_id">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Operador</label>
                                <select name="operador_id" id="operador_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($operadores as $o)
                                        <option value="{{ $o->id }}">{{ $o->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Transportista</label>
                                <select name="transportista_id" id="transportista_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($transportistas as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Vehiculos</label>
                                <select name="vehiculo_id" id="vehiculo_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($vehiculos as $v)
                                        <option value="{{ $v->id }}">{{ $v->placa_identificacion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Tipo de Ingreso</label>
                                <select name="tipo_ingreso_id" id="tipo_ingreso_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($tiposIngreso as $ti)
                                        <option value="{{ $ti->id }}">{{ $ti->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- DETALLE -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Detalle del Ingreso</h5>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0" id="detalleTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad (Pedido)</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="card-footer text-end">
                        <h4>Total: <span id="total">0.00</span> Bs</h4>

                        <a href="{{ route('ingresos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Ingreso</button>
                    </div>

                </div>

            </div>
        </div>
    </form>
</main>

@push('scripts')
<script>
document.getElementById('loadPedido').addEventListener('click', () => {
    let pedidoId = document.getElementById('pedidoSelect').value;
    if (!pedidoId) {
        alert("Seleccione un pedido primero.");
        return;
    }
    const PROVEEDORES = @json($proveedores);
    let pedidos = @json($pedidos);
    let pedido = pedidos.find(p => p.id == pedidoId);
    if (!pedido) return;

    // ✔ ALMACÉN
    document.getElementById('almacen_id').value = pedido.almacen_id;
    document.getElementById('operador_id').value = pedido.operador_id;
    document.getElementById('transportista_id').value = pedido.transportista_id;

    // ✔ PROVEEDOR (obtener nombre desde array)
    let proveedor = PROVEEDORES.find(p => p.id == pedido.proveedor_id);
    document.getElementById('proveedor_nombre').value = proveedor ? proveedor.nombre : "No definido";
    document.getElementById('proveedor_id').value = pedido.proveedor_id;

    // ✔ CARGAR DETALLE
    let tbody = document.querySelector("#detalleTable tbody");
    tbody.innerHTML = "";

    pedido.detalles.forEach((d, index) => {
        let row = `
            <tr>
                <td>${d.producto.nombre}
                    <input type="hidden" name="detalles[${index}][producto_id]" value="${d.producto.id}">
                </td>
                <td>
                    <input type="number" class="form-control" value="${d.cantidad}" readonly>
                    <input type="hidden" name="detalles[${index}][cant_ingreso]" value="${d.cantidad}">
                </td>
                <td>
                    <input type="number" class="form-control precio" 
                        name="detalles[${index}][precio]" step="0.0001" min="0">
                </td>
                <td class="subtotal">0.00</td>
            </tr>
        `;
        tbody.insertAdjacentHTML("beforeend", row);
    });

    calcularTotales();
});

// Recalcular subtotales
document.addEventListener('input', e => {
    if (e.target.classList.contains('precio')) {
        calcularTotales();
    }
});

function calcularTotales() {
    let total = 0;

    document.querySelectorAll("#detalleTable tbody tr").forEach(tr => {
        let cantidad = Number(tr.querySelector("input[name*='cant_ingreso']").value);
        let precio = Number(tr.querySelector(".precio").value);
        let subtotal = cantidad * precio;

        tr.querySelector(".subtotal").innerText = subtotal.toFixed(2);
        total += subtotal;
    });

    document.getElementById("total").innerText = total.toFixed(2);
}
</script>
@endpush

@endsection
