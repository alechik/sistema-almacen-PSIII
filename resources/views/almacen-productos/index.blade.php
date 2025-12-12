@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Productos asignados a almacenes</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Almacén - Productos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Mensajes -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tarjeta -->
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Lista de Productos por Almacén</h3>
                    <a href="{{ route('almacen-productos.create') }}" class="btn btn-primary btn-sm ms-auto">
                        <i class="fas fa-plus"></i> Asignar Producto
                    </a>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="text-center align-middle">
                            <tr>
                                <th>ID</th>
                                <th>Almacén</th>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Unidad</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                                <th>En Pedido</th>
                                <th width="150px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($almacenProductos as $ap)
                            <tr class="align-middle">

                                <td class="text-center">{{ $ap->id }}</td>

                                <td>{{ $ap->almacen->nombre ?? '-' }}</td>

                                <td class="text-center fw-bold">
                                    {{ $ap->producto->cod_producto }}
                                </td>

                                <td>
                                    {{ $ap->producto->nombre }}
                                </td>

                                <td class="text-center">
                                    @if ($ap->producto->unidadMedida)
                                        {{ $ap->producto->unidadMedida->cod_unidad_medida }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $ap->producto->unidadMedida->descripcion }}
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    {{-- {{ $ap->stock }} --}}
                                    @if ($ap->stock <= $ap->stock_minimo)
                                        <span class="badge bg-danger">
                                            {{ $ap->stock }}
                                        </span>
                                    @elseif ($ap->stock > $ap->stock_minimo * 3)
                                        <span class="badge bg-success">
                                            {{ $ap->stock }}
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark">
                                            {{ $ap->stock }}
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    {{ $ap->stock_minimo }}
                                </td>

                                <td class="text-center">
                                    @if ($ap->en_pedido == 0)
                                        <span class="badge bg-secondary">No</span>
                                    @elseif ($ap->en_pedido == 1)
                                        <span class="badge bg-warning text-dark">En pedido</span>
                                    @else
                                        <span class="badge bg-danger">Necesita</span>
                                    @endif
                                </td>

                                <td class="text-center">

                                    <a href="{{ route('almacen-productos.show', $ap) }}" 
                                       class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('almacen-productos.edit', $ap) }}" 
                                       class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form action="{{ route('almacen-productos.destroy', $ap) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" hidden>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="9" class="text-center p-3">
                                    No existen productos asignados a ningún almacén.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-end">
                        {{ $almacenProductos->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

@endsection
