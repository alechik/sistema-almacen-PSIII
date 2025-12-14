@extends('dashboard-layouts.header-footer')
@section('content')

<main class="app-main">

    <!-- HEADER -->
    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Registrar Pedido por Stock Mínimo</h3>

            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pedidos.index') }}">Pedidos</a></li>
                <li class="breadcrumb-item active">Nuevo por Stock Mínimo</li>
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
                            <!-- ALMACÉN -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Almacén *</label>
                                <input type="text" class="form-control" value="{{ $almacen->nombre }}" readonly>
                                <input type="hidden" name="almacen_id" value="{{ $almacen->id }}">
                            </div>

                            <!-- PROVEEDOR -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Proveedor *</label>
                                <input type="text" class="form-control"
                                       value="{{ collect($proveedores)->firstWhere('id', $proveedorId)['nombre'] }}"
                                       readonly>
                                <input type="hidden" name="proveedor_id" value="{{ $proveedorId }}">
                            </div>
                        </div>

                        <div class="row">
                            <!-- FECHA -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha *</label>
                                <input type="date" name="fecha" class="form-control" value="{{ now()->format('Y-m-d') }}">
                            </div>

                            <!-- FECHA MÍNIMA -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha Mínima *</label>
                                <input type="date" name="fecha_min" class="form-control" value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="row">
                            <!-- FECHA MÁXIMA -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fecha Máxima *</label>
                                <input type="date" name="fecha_max" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d') }}">
                            </div>

                            <!-- OPERADOR -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Operador *</label>
                                <select name="operador_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($operadores as $o)
                                        <option value="{{ $o->id }}">{{ $o->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <!-- TRANSPORTISTA -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Transportista *</label>
                                <select name="transportista_id" class="form-select" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($transportistas as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================== -->
                <!-- DETALLE DEL PEDIDO            -->
                <!-- ============================== -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Detalle del Pedido</h5>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th style="width:150px">Cantidad</th>
                                    <th style="width:120px">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productos as $i => $producto)
                                    @php
                                        $pivot = $producto->almacenes->first()->pivot;
                                        $cantidadSugerida = max(
                                            $pivot->stock_minimo - $pivot->stock,
                                            1
                                        );
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $producto->nombre }}
                                            <input type="hidden"
                                                   name="productos[{{ $i }}][producto_id]"
                                                   value="{{ $producto->id }}">
                                        </td>

                                        <td>
                                            <input type="number"
                                                   name="productos[{{ $i }}][cantidad]"
                                                   value="{{ $cantidadSugerida }}"
                                                   min="1"
                                                   class="form-control">
                                        </td>

                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm btnDelete">Quitar</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
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
    document.addEventListener('click', e => {
        if (e.target.classList.contains('btnDelete')) {
            e.target.closest('tr').remove();
        }
    });
</script>
@endpush

@endsection
