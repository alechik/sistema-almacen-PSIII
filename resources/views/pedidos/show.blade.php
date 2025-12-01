@extends('dashboard-layouts.header-footer')
@section('content')

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Detalle del Pedido</h3>
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pedidos.index') }}">Pedidos</a></li>
                <li class="breadcrumb-item active">{{ $pedido->codigo_comprobante }}</li>
            </ol>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <!-- DATOS GENERALES -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Datos del Pedido</h5>
                </div>
                <div class="card-body">

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Código Comprobante:</strong>
                            <p>{{ $pedido->codigo_comprobante }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Fecha:</strong>
                            <p>{{ $pedido->fecha }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Estado:</strong>
                            <span class="badge bg-info">
                                    {{-- const CANCELADO = 0;
                                    const EMITIDO = 1;
                                    const CONFIRMADO = 2;
                                    const TERMINADO = 3;
                                    const ANULADO = 4; --}}
                                @if ($pedido->estado == 0)
                                    CANCELADO
                                @elseif ($pedido->estado == 1)
                                    EMITIDO
                                @elseif ($pedido->estado == 2)
                                    CONFIRMADO
                                @elseif ($pedido->estado == 3)
                                    TERMINADO
                                @else
                                    ANULADO
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Fecha Mínima:</strong>
                            <p>{{ $pedido->fecha_min }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Fecha Máxima:</strong>
                            <p>{{ $pedido->fecha_max }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Almacén:</strong>
                            <p>{{ $pedido->almacen->nombre }}</p>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-4">
                            <strong>Proveedor:</strong>
                            <p>
                                {{
                                    collect($proveedores)
                                        ->firstWhere('id', $pedido->proveedor_id)['nombre']
                                        ?? 'No definido'
                                }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <strong>Operador:</strong>
                            <p>{{ $pedido->operador->full_name }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Transportista:</strong>
                            <p>{{ $pedido->transportista->full_name }}</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- DETALLE -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Detalle del Pedido</h5>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th style="width: 20%">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedido->detalles as $d)
                                <tr>
                                    <td>{{ $d->producto->nombre }}</td>
                                    <td class="text-center">{{ $d->cantidad }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer text-end">
                    <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>

        </div>
    </div>

</main>

@endsection
