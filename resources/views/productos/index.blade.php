@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Listado de Productos</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Productos</li>
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

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Hay errores:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tarjeta -->
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Lista de Productos</h3>
                    <!-- Botones a la derecha -->
                    <div class="ms-auto d-flex gap-2">
                        <form action="{{ route('productos.sync.planta') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm">
                                🔄 Actualizar productos desde Planta
                            </button>
                        </form>

                        <a href="{{ route('productos.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Producto
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="text-center align-middle">
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Unidad</th>
                                {{-- <th>Stock</th> --}}
                                <th>Categoría</th>
                                <th>Proveedor</th>
                                <th>Estado</th>
                                <th width="150px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($productos as $producto)
                            <tr class="align-middle">

                                <td class="text-center">{{ $producto->id }}</td>

                                <td class="text-center fw-bold">{{ $producto->cod_producto }}</td>

                                <td>{{ $producto->nombre }}</td>

                                {{-- NUEVA COLUMNA UNIDAD DE MEDIDA --}}
                                <td class="text-center">
                                    @if ($producto->unidadMedida)
                                        <span class="fw-bold">
                                            {{ $producto->unidadMedida->cod_unidad_medida }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $producto->unidadMedida->descripcion }}
                                        </small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- STOCK MEJORADO --}}
                                {{-- <td class="text-center">
                                    @if ($producto->stock <= $producto->stock_minimo)
                                        <span class="badge bg-danger">
                                            {{ $producto->stock }}
                                        </span>
                                    @elseif ($producto->stock > $producto->stock_minimo * 3)
                                        <span class="badge bg-success">
                                            {{ $producto->stock }}
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark">
                                            {{ $producto->stock }}
                                        </span>
                                    @endif
                                </td> --}}

                                <td>{{ $producto->categoria->nombre ?? '-' }}</td>

                                <td>
                                    @php
                                        $proveedor = collect($proveedores)->firstWhere('id', $producto->proveedor_id);
                                    @endphp
                                    {{ $proveedor['nombre'] ?? '-' }}
                                </td>

                                {{-- ESTADO --}}
                                <td class="text-center">
                                    @if ($producto->estado == 1 || $producto->estado == 'activo')
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger">Inactivo</span>
                                    @endif
                                </td>

                                <!-- ACCIONES -->
                                <td class="text-center">
                                    <a href="{{ route('productos.show', $producto) }}" 
                                    class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('productos.edit', $producto) }}" 
                                    class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <button 
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar"
                                        data-id="{{ $producto->id }}"
                                        data-nombre="{{ $producto->nombre }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center p-3">
                                    No existen productos registrados.
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

<!-- Modal eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el producto?</p>
                <h5 class="fw-bold text-danger" id="nombreProducto"></h5>
                <p>Esta acción no se puede deshacer.</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                <form id="formEliminar" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        Eliminar
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modalEliminar = document.getElementById('modalEliminar');

    modalEliminar.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const nombre = button.getAttribute('data-nombre');

        document.getElementById('nombreProducto').textContent = nombre;

        const form = document.getElementById('formEliminar');
        form.action = `/productos/${id}`;
    });
</script>
@endpush
