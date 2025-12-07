@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-7">
                    <h3 class="mb-0">Editar Ingreso N° {{ $ingreso->codigo_comprobante }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="card card-body shadow-lg">

                <form action="{{ route('ingresos.update', $ingreso->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- ================================
                        DATOS GENERALES
                    ================================= --}}
                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label class="form-label">Código Comprobante</label>
                            <input type="text"
                                   name="codigo_comprobante"
                                   class="form-control"
                                   value="{{ old('codigo_comprobante', $ingreso->codigo_comprobante) }}"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date"
                                   name="fecha"
                                   class="form-control"
                                   value="{{ old('fecha', $ingreso->fecha) }}"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha Mínima</label>
                            <input type="date"
                                   name="fecha_min"
                                   class="form-control"
                                   value="{{ old('fecha_min', $ingreso->fecha_min) }}"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha Máxima</label>
                            <input type="date"
                                   name="fecha_max"
                                   class="form-control"
                                   value="{{ old('fecha_max', $ingreso->fecha_max) }}"
                                   required>
                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label class="form-label">Tipo de Ingreso</label>
                            <select name="tipo_ingreso_id" class="form-control" required>
                                @foreach($tiposIngreso as $tipo)
                                    <option value="{{ $tipo->id }}"
                                        @selected($tipo->id == $ingreso->tipo_ingreso_id)>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Pedido Asociado</label>
                            <select name="pedido_id" class="form-control" required>
                                <option value="{{ $ingreso->pedido->id }}">
                                    {{ $ingreso->pedido->codigo_comprobante }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Proveedor</label>
                            <select name="proveedor_id" class="form-control" required>
                                @foreach($proveedores as $p)
                                    <option value="{{ $p['id'] }}"
                                        @selected($p['id'] == $ingreso->proveedor_id)>
                                        {{ $p['nombre'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Vehículo</label>
                            <select name="vehiculo_id" class="form-control">
                                <option value="">Sin vehículo</option>
                                @foreach($vehiculos as $veh)
                                    <option value="{{ $veh->id }}"
                                        @selected($veh->id == $ingreso->vehiculo_id)>
                                        {{ $veh->placa_identificacion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="row mb-3">

                        <div class="col-md-4">
                            <label class="form-label">Almacén</label>
                            <select name="almacen_id" class="form-control" required>
                                @foreach($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}"
                                        @selected($almacen->id == $ingreso->almacen_id)>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Operador</label>
                            <select name="operador_id" class="form-control" required>
                                @foreach($operadores as $op)
                                    <option value="{{ $op->id }}"
                                        @selected($op->id == $ingreso->operador_id)>
                                        {{ $op->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Transportista</label>
                            <select name="transportista_id" class="form-control" required>
                                @foreach($transportistas as $t)
                                    <option value="{{ $t->id }}"
                                        @selected($t->id == $ingreso->transportista_id)>
                                        {{ $t->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- ================================
                        DETALLES
                    ================================= --}}
                    <hr>
                    <h5>Detalle del Ingreso</h5>

                    <table class="table table-bordered mt-3" id="tabla-detalles">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th width="120">Cantidad</th>
                                <th width="120">Precio</th>
                                <th width="120">Subtotal</th>
                                <th width="50"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($ingreso->detalles as $i => $detalle)
                                <tr>
                                    <td>
                                        <select name="detalles[{{ $i }}][producto_id]" class="form-control" required>
                                            @foreach($productos as $prod)
                                                <option value="{{ $prod->id }}"
                                                    @selected($prod->id == $detalle->producto_id)>
                                                    {{ $prod->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="detalles[{{ $i }}][cant_ingreso]"
                                               class="form-control cantidad"
                                               min="0" step="0.0001"
                                               value="{{ $detalle->cant_ingreso }}" required>
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="detalles[{{ $i }}][precio]"
                                               class="form-control precio"
                                               min="0" step="0.0001"
                                               value="{{ $detalle->precio }}" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control subtotal"
                                               value="{{ $detalle->cant_ingreso * $detalle->precio }}" readonly>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm eliminar-fila">X</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-primary mb-3" id="agregar-fila">
                        + Agregar Producto
                    </button>

                    {{-- TOTAL --}}
                    <div class="row">
                        <div class="col-md-3 ms-auto">
                            <label class="form-label">Total</label>
                            <input type="text" id="total" class="form-control"
                                   value="{{ $ingreso->detalles->sum(fn($d) => $d->cant_ingreso * $d->precio) }}"
                                   readonly>
                        </div>
                    </div>

                    {{-- BOTONES --}}
                    <div class="mt-4 text-end">
                        <a href="{{ route('ingresos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>

                </form>

            </div>

        </div>
    </div>

</main>

{{-- ================================
  SCRIPT
================================= --}}
<script>
    function recalcularTotales() {
        let total = 0;

        document.querySelectorAll('#tabla-detalles tbody tr').forEach(row => {
            const c = parseFloat(row.querySelector('.cantidad').value) || 0;
            const p = parseFloat(row.querySelector('.precio').value) || 0;

            const subtotal = c * p;
            row.querySelector('.subtotal').value = subtotal.toFixed(4);

            total += subtotal;
        });

        document.getElementById('total').value = total.toFixed(2);
    }

    document.addEventListener('input', e => {
        if (e.target.classList.contains('cantidad') || e.target.classList.contains('precio')) {
            recalcularTotales();
        }
    });

    document.getElementById('agregar-fila').addEventListener('click', function () {
        let index = document.querySelectorAll('#tabla-detalles tbody tr').length;

        let fila = `
            <tr>
                <td>
                    <select name="detalles[${index}][producto_id]" class="form-control" required>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" step="0.0001" min="0"
                           name="detalles[${index}][cant_ingreso]"
                           class="form-control cantidad" required></td>

                <td><input type="number" step="0.0001" min="0"
                           name="detalles[${index}][precio]"
                           class="form-control precio" required></td>

                <td><input type="text" class="form-control subtotal" value="0.0000" readonly></td>

                <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">X</button></td>
            </tr>
        `;

        document.querySelector('#tabla-detalles tbody').insertAdjacentHTML('beforeend', fila);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('eliminar-fila')) {
            e.target.closest('tr').remove();
            recalcularTotales();
        }
    });
</script>

@endsection
