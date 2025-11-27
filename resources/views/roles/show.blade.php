@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalles del Rol</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                        <li class="breadcrumb-item active">Ver Rol</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Tarjeta principal -->
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Información del Rol</h3>

                    <div class="ms-auto">
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Nombre del Rol:</label>
                            <p>{{ $role->name }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Guard:</label>
                            <p>{{ $role->guard_name }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Fecha de creación:</label>
                            <p>{{ $role->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Última actualización:</label>
                            <p>{{ $role->updated_at->format('d/m/Y H:i') }}</p>
                        </div>

                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al listado
                    </a>
                </div>

            </div>

        </div>
    </div>

</main>

{{-- Modal de confirmación para eliminar --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                ¿Está seguro de que desea eliminar el rol
                <strong>{{ $role->name }}</strong>?
                <br>
                Esta acción no se puede deshacer.
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                <form action="{{ route('roles.destroy', $role) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection
