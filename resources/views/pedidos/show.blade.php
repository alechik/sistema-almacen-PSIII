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
                    @hasrole('propietario')
                        @if ($pedido->estado != \App\Models\Pedido::CANCELADO || $pedido->estado != \App\Models\Pedido::TERMINADO)
                            @if ($pedido->estado == \App\Models\Pedido::EMITIDO)
                                <!-- Botón Confirmar -->
                                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#confirmarModal">
                                    Confirmar
                                </button>

                                <!-- Botón Anular -->
                                <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#anularModal">
                                    Anular
                                </button>
                            @endif
                        @endif
                    @endhasrole
                    <a href="{{ route('pedidos.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>

        </div>
    </div>

</main>
<!-- MODAL CONFIRMAR -->
<div class="modal fade" id="confirmarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Confirmar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-3">Confirma el siguiente pedido:</p>
                <ul class="list-group mb-3">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-6"><strong>Código:</strong> {{ $pedido->codigo_comprobante }}</div>
                            <div class="col-6"><strong>Fecha:</strong> {{ $pedido->fecha }}</div>
                        </div>
                    </li>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-6"><strong>Almacén:</strong> {{ $pedido->almacen->nombre }}</div>
                            <div class="col-6"><strong>Proveedor:</strong>
                                {{
                                    collect($proveedores)
                                        ->firstWhere('id', $pedido->proveedor_id)['nombre']
                                        ?? 'No definido'
                                }}
                            </div>
                        </div>
                    </li>
                </ul>
                <h6 class="text-uppercase fw-bold">Detalle del Pedido</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center" style="width: 25%">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->nombre }}</td>
                                <td class="text-center">{{ $detalle->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p class="text-muted small mt-2">Esta acción no se puede revertir.</p>
            </div>

            <div class="modal-footer">
                <form method="POST" action="{{ route('pedidos.confirmar', $pedido->id) }}">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success">Sí, Confirmar</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL ANULAR -->
<div class="modal fade" id="anularModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Anular Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-3 text-danger fw-semibold">¿Seguro que deseas anular este pedido?</p>

                <ul class="list-group mb-3">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-6"><strong>Código:</strong> {{ $pedido->codigo_comprobante }}</div>
                            <div class="col-6"><strong>Fecha:</strong> {{ $pedido->fecha }}</div>
                        </div>
                    </li>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-6"><strong>Almacén:</strong> {{ $pedido->almacen->nombre }}</div>
                            <div class="col-6"><strong>Proveedor:</strong>
                                {{
                                    collect($proveedores)
                                        ->firstWhere('id', $pedido->proveedor_id)['nombre']
                                        ?? 'No definido'
                                }}
                            </div>
                        </div>
                    </li>
                </ul>


                <h6 class="text-uppercase fw-bold">Detalle del Pedido</h6>
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center" style="width: 25%">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pedido->detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->producto->nombre }}</td>
                                <td class="text-center">{{ $detalle->cantidad }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <p class="text-muted small mt-2">Al anular este pedido ya no podrá ser confirmado.</p>
            </div>


            <div class="modal-footer">
                <form method="POST" action="{{ route('pedidos.anular', $pedido->id) }}">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-danger">Sí, Anular</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

@endsection
