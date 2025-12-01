@extends('dashboard-layouts.header-footer')
@section('content')

<main class="app-main">

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
    
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
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

                <!-- CABECERA DEL PEDIDO -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Datos del Pedido</h5>
                    </div>
                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Código Comprobante *</label>
                                <input type="text" name="codigo_comprobante" class="form-control"
                                    value="{{ (Auth::user()->user_id ?? Auth::user()->id) * 1000000 + (($lastId ?? 0) + 1) }}"
                                    readonly>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha *</label>
                                <input type="date" name="fecha" class="form-control" value="{{ old('fecha', now()->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Mínima *</label>
                                <input type="date" name="fecha_min" class="form-control"
                                    value="{{ old('fecha_min', now()->format('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fecha Máxima *</label>
                                <input type="date" name="fecha_max" class="form-control"
                                    value="{{ old('fecha_max', now()->addDays(7)->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Almacén *</label>
                                <select name="almacen_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($almacenes as $a)
                                        <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Proveedor *</label>
                                <select name="proveedor_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($proveedores as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['nombre'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Operador *</label>
                                <select name="operador_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($operadores as $o)
                                        <option value="{{ $o->id }}">{{ $o->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Transportista *</label>
                                <select name="transportista_id" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($transportistas as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- DETALLE -->
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
                                    <th style="width: 55%">Producto</th>
                                    <th style="width: 25%">Cantidad</th>
                                    <th style="width: 20%">Acción</th>
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

    document.getElementById('addRow').addEventListener('click', () => {
        const tbody = document.querySelector('#detalleTable tbody');

        const row = `
            <tr>
                <td>
                    <select name="productos[${index}][producto_id]" class="form-select" required>
                        <option value="">Seleccione producto...</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->nombre }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" name="productos[${index}][cantidad]" 
                        min="1" class="form-control" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btnDelete">X</button>
                </td>
            </tr>
        `;

        tbody.insertAdjacentHTML('beforeend', row);
        index++; // Aumenta índice para la siguiente fila
    });

    // Eliminar fila
    document.addEventListener('click', e => {
        if (e.target.classList.contains('btnDelete')) {
            e.target.closest('tr').remove();
        }
    });
    </script>
@endpush


@endsection
