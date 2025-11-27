@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Listado de Tipos de Ingreso</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Tipos de Ingreso</li>
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

            <!-- Tarjeta principal -->
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Lista de Tipos de Ingreso</h3>

                    <a href="{{ route('tipoingresos.create') }}" class="btn btn-primary btn-sm ms-auto">
                        <i class="fas fa-plus"></i> Nuevo Tipo de Ingreso
                    </a>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th width="150px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($tipos as $tipo)
                            <tr class="align-middle">
                                <td>{{ $tipo->id }}</td>
                                <td>{{ $tipo->nombre }}</td>
                                <td>{{ $tipo->descripcion }}</td>

                                <td class="text-center">

                                    <a href="{{ route('tipoingresos.show', $tipo) }}" 
                                       class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('tipoingresos.edit', $tipo->id) }}" 
                                       class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Botón de eliminar -->
                                    <button 
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar"
                                        data-id="{{ $tipo->id }}"
                                        data-nombre="{{ $tipo->nombre }}">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center p-3">
                                    No existen tipos de ingreso registrados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>

                <!-- Paginación -->
                <div class="card-footer clearfix">
                    <div class="float-end">
                        {{ $tipos->links('pagination::bootstrap-5') }}
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
                <p>¿Está seguro que desea eliminar el tipo de ingreso?</p>
                <h5 class="fw-bold text-danger" id="nombreTipoIngreso"></h5>
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

        document.getElementById('nombreTipoIngreso').textContent = nombre;

        const form = document.getElementById('formEliminar');
        form.action = `/tipoingresos/${id}`;
    });
</script>
@endpush