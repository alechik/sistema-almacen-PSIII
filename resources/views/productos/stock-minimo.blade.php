@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Productos con Stock Mínimo</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Stock mínimo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Tarjeta -->
            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">
                        Lista de productos por debajo del stock mínimo
                    </h3>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="text-center align-middle">
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Almacén</th>
                                <th>Stock</th>
                                <th>Stock mínimo</th>
                                <th>Estado</th>
                                <th width="160px">Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($productos as $producto)
                            @foreach ($producto->almacenes as $almacen)
                                <tr class="align-middle">

                                    <td class="text-center">{{ $producto->id }}</td>

                                    <td class="text-center fw-bold">
                                        {{ $producto->cod_producto }}
                                    </td>

                                    <td>
                                        {{ $producto->nombre }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $producto->categoria->nombre ?? '-' }}
                                        </small>
                                    </td>

                                    <td class="text-center">
                                        <span class="fw-bold">
                                            {{ $almacen->nombre }}
                                        </span>
                                    </td>

                                    {{-- STOCK --}}
                                    <td class="text-center">
                                        <span class="badge bg-danger">
                                            {{ $almacen->pivot->stock }}
                                        </span>
                                    </td>

                                    {{-- STOCK MÍNIMO --}}
                                    <td class="text-center">
                                        {{ $almacen->pivot->stock_minimo }}
                                    </td>

                                    {{-- ESTADO --}}
                                    <td class="text-center">
                                        @if ($almacen->pivot->en_pedido == 0)
                                            <span class="badge bg-warning text-dark">
                                                Pendiente de pedido
                                            </span>
                                        @elseif ($almacen->pivot->en_pedido == 1)
                                            <span class="badge bg-info">
                                                En pedido
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                Atendido
                                            </span>
                                        @endif
                                    </td>

                                    {{-- ACCIÓN --}}
                                    <td class="text-center">
                                        <a href="{{ route('pedidos.stock-minimo.create', [
                                                'almacen' => $almacen->id,
                                                'proveedor' => $producto->proveedor_id
                                                ]) }}"
                                            class="btn btn-warning btn-sm">
                                            <i class="bi bi-cart-plus"></i>
                                            Generar pedido
                                        </a>
                                    </td>

                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-3">
                                    No existen productos con stock mínimo.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="card-footer clearfix">
                    <div class="float-end">
                        {{ $productos->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

@endsection
