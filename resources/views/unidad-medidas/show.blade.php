@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalles de Unidad de Medida</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('unidad-medidas.index') }}">Unidades de Medida</a></li>
                        <li class="breadcrumb-item active">Ver Unidad</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Información de la Unidad de Medida</h3>

                    <div>
                        <a href="{{ route('unidad-medidas.edit', $unidadMedida) }}" class="btn btn-warning btn-sm">
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
                            <label class="fw-bold">Código:</label>
                            <p>{{ $unidadMedida->cod_unidad_medida }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Descripción:</label>
                            <p>{{ $unidadMedida->descripcion }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Fecha de creación:</label>
                            <p>{{ $unidadMedida->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Última actualización:</label>
                            <p>{{ $unidadMedida->updated_at->format('d/m/Y H:i') }}</p>
                        </div>

                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('unidad-medidas.index') }}" class="btn btn-secondary">
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
                ¿Está seguro de que desea eliminar la unidad de medida
                <strong>{{ $unidadMedida->descripcion }}</strong>?
                <br>
                Esta acción no se puede deshacer.
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                <form action="{{ route('unidad-medidas.destroy', $unidadMedida) }}" method="POST">
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
