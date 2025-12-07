@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Registrar Salida</h3>
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('salidas.index') }}">Salidas</a></li>
                <li class="breadcrumb-item active">Nueva</li>
            </ol>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }} <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <form action="{{ route('salidas.store') }}" method="POST">
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

                <!-- DATOS PRINCIPALES -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Datos de la Salida</h5>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Código Comprobante</label>
                                <input type="text" class="form-control"
                                    value="{{ 'S' . ((Auth::user()->user_id ?? Auth::user()->id) * 1000000 + (($lastId ?? 0) + 1)) }}"
                                    name="codigo_comprobante" readonly>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Nota Venta (Correlativa)</label>
                                <input type="text" class="form-control"
                                    value="{{ ($lastNota ?? 0) + 1 }}"
                                    name="nota_venta_id" readonly>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Fecha</label>
                                <input type="date" class="form-control" name="fecha"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Fecha Mínima</label>
                                <input type="date" class="form-control" name="fecha_min"
                                    value="{{ now()->format('Y-m-d') }}">
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Máxima</label>
                                <input type="date" class="form-control" name="fecha_max"
                                    value="{{ now()->addDays(7)->format('Y-m-d') }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Punto de Venta</label>
                                <select name="punto_venta_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($puntosVenta as $pv)
                                        <option value="{{ $pv['id'] }}">{{ $pv['nombre'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- DATOS OPERACIONALES -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Operación</h5>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Almacén</label>
                                <select class="form-select" name="almacen_id">
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $a)
                                    <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Operador</label>
                                <select class="form-select" name="operador_id">
                                    @foreach($operadores as $o)
                                        <option value="{{ $o->id }}">{{ $o->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Transportista</label>
                                <select class="form-select" name="transportista_id">
                                    @foreach($transportistas as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Vehículos</label>
                                <select class="form-select" name="vehiculo_id">
                                    @foreach($vehiculos as $v)
                                    <option value="{{ $v->id }}">{{ $v->placa_identificacion }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Tipo Salida</label>
                                <select name="tipo_salida_id" class="form-select">
                                    @foreach($tiposSalida as $ts)
                                        <option value="{{ $ts->id }}">{{ $ts->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- DETALLE -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <h5 class="mb-0">Detalle de Salida</h5>
                        <button type="button" class="btn btn-light btn-sm" id="addRow">
                            + Agregar Producto
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0" id="detalleTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="card-footer text-end">
                        <h4>Total: <span id="total">0.00</span> Bs</h4>

                        <a href="{{ route('salidas.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Salida</button>
                    </div>
                </div>

            </div>
        </div>
    </form>

</main>

@push('scripts')
<script>
let productos = @json($productos);
let index = 0;

document.getElementById('addRow').addEventListener('click', () => {

    let row = `
        <tr>
            <td>
                <select name="detalles[${index}][producto_id]" class="form-select">
                    ${productos.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('')}
                </select>
            </td>

            <td>
                <input type="number" name="detalles[${index}][cant_salida]" 
                    step="0.01" min="0.01" class="form-control cantidad">
            </td>

            <td>
                <input type="number" name="detalles[${index}][precio]" 
                    step="0.0001" min="0.0001" class="form-control precio">
            </td>

            <td class="subtotal">0.00</td>

            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
        </tr>
    `;

    document.querySelector("#detalleTable tbody").insertAdjacentHTML("beforeend", row);
    index++;
});

document.addEventListener('input', e => {
    if (e.target.classList.contains('cantidad') || e.target.classList.contains('precio')) {
        recalcular();
    }
});

document.addEventListener('click', e => {
    if (e.target.classList.contains('removeRow')) {
        e.target.closest('tr').remove();
        recalcular();
    }
});

function recalcular() {
    let total = 0;

    document.querySelectorAll("#detalleTable tbody tr").forEach(tr => {
        let c = Number(tr.querySelector(".cantidad")?.value || 0);
        let p = Number(tr.querySelector(".precio")?.value || 0);
        let sub = c * p;

        tr.querySelector(".subtotal").innerText = sub.toFixed(2);
        total += sub;
    });

    document.getElementById('total').innerText = total.toFixed(2);
}
</script>
@endpush

@endsection
