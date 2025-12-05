@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
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

                    {{-- CABECERA --}}
                    <div class="row mb-3">

                        <div class="col-md-3">
                            <label class="form-label">Fecha</label>
                            <input type="date"
                                   name="fecha"
                                   class="form-control"
                                   value="{{ old('fecha', $ingreso->fecha) }}"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Tipo de Ingreso</label>
                            <select name="tipo_ingreso_id" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                @foreach($tiposIngreso as $tipo)
                                    <option value="{{ $tipo->id }}"
                                        {{ $tipo->id == $ingreso->tipo_ingreso_id ? 'selected' : '' }}>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Almacén</label>
                            <select name="almacen_id" class="form-control" required>
                                @foreach($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}"
                                        {{ $almacen->id == $ingreso->almacen_id ? 'selected' : '' }}>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Responsable</label>
                            <input type="text" class="form-control"
                                   value="{{ $ingreso->administrador->name }}" disabled>
                        </div>
                    </div>

                    {{-- DETALLES --}}
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
                                            <option value="">-- Seleccione --</option>
                                            @foreach($productos as $prod)
                                                <option value="{{ $prod->id }}"
                                                    {{ $prod->id == $detalle->producto_id ? 'selected' : '' }}>
                                                    {{ $prod->nombre }} ({{ $prod->unidadMedida->nombre }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input type="number" class="form-control cantidad"
                                               step="0.0001" min="0"
                                               name="detalles[{{ $i }}][cant_ingreso]"
                                               value="{{ $detalle->cant_ingreso }}" required>
                                    </td>

                                    <td>
                                        <input type="number" class="form-control precio"
                                               step="0.0001" min="0"
                                               name="detalles[{{ $i }}][precio]"
                                               value="{{ $detalle->precio }}" required>
                                    </td>

                                    <td>
                                        <input type="text" class="form-control subtotal"
                                               value="{{ number_format($detalle->cant_ingreso * $detalle->precio, 4, '.', '') }}"
                                               readonly>
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm eliminar-fila">
                                            X
                                        </button>
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
                            <input type="text" name="total" id="total" class="form-control"
                                   value="{{ number_format($ingreso->detalles->sum(fn($d) => $d->cant_ingreso * $d->precio), 2, '.', '') }}"
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

{{-- SCRIPT DINÁMICO --}}
<script>
    function recalcularTotales() {
        let total = 0;

        document.querySelectorAll('#tabla-detalles tbody tr').forEach(function (row) {
            const cantidad = parseFloat(row.querySelector('.cantidad').value) || 0;
            const precio = parseFloat(row.querySelector('.precio').value) || 0;

            const subtotal = cantidad * precio;
            row.querySelector('.subtotal').value = subtotal.toFixed(4);

            total += subtotal;
        });

        document.getElementById('total').value = total.toFixed(2);
    }

    document.addEventListener('input', function (e) {
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
                        <option value="">-- Seleccione --</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id }}">
                                {{ $prod->nombre }} ({{ $prod->unidadMedida->nombre }})
                            </option>
                        @endforeach
                    </select>
                </td>

                <td>
                    <input type="number" step="0.0001" min="0"
                           name="detalles[${index}][cant_ingreso]"
                           class="form-control cantidad" required>
                </td>

                <td>
                    <input type="number" step="0.0001" min="0"
                           name="detalles[${index}][precio]"
                           class="form-control precio" required>
                </td>

                <td>
                    <input type="text" class="form-control subtotal" readonly>
                </td>

                <td>
                    <button type="button" class="btn btn-danger btn-sm eliminar-fila">X</button>
                </td>
            </tr>
        `;

        document.querySelector('#tabla-detalles tbody').insertAdjacentHTML('beforeend', fila);
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('eliminar-fila')) {
            e.target.closest('tr').remove();
            recalcularTotales();
        }
    });

</script>

@endsection
