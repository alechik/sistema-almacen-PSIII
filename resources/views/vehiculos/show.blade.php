@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalles del Vehículo</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('vehiculos.index') }}">Vehículos</a></li>
                        <li class="breadcrumb-item active">Ver Vehículo</li>
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
                    <h3 class="card-title">Información Registrada</h3>

                    <div class="ms-auto">
                        <a href="{{ route('vehiculos.edit', $vehiculo) }}" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalEliminar">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </div>
                </div>

                <div class="card-body">

                    <!-- ID -->
                    <div class="mb-3">
                        <label class="fw-bold">ID:</label>
                        <p>{{ $vehiculo->id }}</p>
                    </div>

                    <!-- Placa -->
                    <div class="mb-3">
                        <label class="fw-bold">Placa de Identificación:</label>
                        <p>{{ $vehiculo->placa_identificacion }}</p>
                    </div>

                    <!-- Marca y Modelo -->
                    <div class="mb-3">
                        <label class="fw-bold">Marca / Modelo:</label>
                        <p>{{ $vehiculo->marca_modelo }}</p>
                    </div>

                    <!-- Año -->
                    <div class="mb-3">
                        <label class="fw-bold">Año:</label>
                        <p>{{ $vehiculo->anio ?? 'Sin especificar' }}</p>
                    </div>

                    <!-- Fecha creación -->
                    <div class="mb-3">
                        <label class="fw-bold">Fecha de creación:</label>
                        <p>{{ $vehiculo->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <!-- Fecha actualización -->
                    <div class="mb-3">
                        <label class="fw-bold">Última actualización:</label>
                        <p>{{ $vehiculo->updated_at->format('d/m/Y H:i') }}</p>
                    </div>

                </div>

                <!-- Footer -->
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al listado
                    </a>
                </div>

            </div>

        </div>
    </div>

</main>

{{-- Modal para eliminar --}}
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalEliminarLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                ¿Está seguro de que desea eliminar el vehículo con placa
                <strong>{{ $vehiculo->placa_identificacion }}</strong>?
                <br>
                Esta acción no se puede deshacer.
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                <form action="{{ route('vehiculos.destroy', $vehiculo) }}" method="POST">
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
