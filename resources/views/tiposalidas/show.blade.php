@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalles del Tipo de Salida</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tiposalidas.index') }}">Tipos de Salida</a></li>
                        <li class="breadcrumb-item active">Detalles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Información del Tipo de Salida</h3>
                    <a href="{{ route('tiposalidas.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre:</label>
                        <p class="form-control-plaintext">{{ $tipoSalida->nombre }}</p>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción:</label>
                        <p class="form-control-plaintext">
                            {{ $tipoSalida->descripcion ? $tipoSalida->descripcion : 'Sin descripción' }}
                        </p>
                    </div>

                    <!-- Fecha de creación -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Fecha de registro:</label>
                        <p class="form-control-plaintext">{{ $tipoSalida->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <!-- Fecha de última actualización -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Última actualización:</label>
                        <p class="form-control-plaintext">{{ $tipoSalida->updated_at->format('d/m/Y H:i') }}</p>
                    </div>

                </div>

                <div class="card-footer text-end">

                    <a href="{{ route('tiposalidas.edit', $tipoSalida) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Editar
                    </a>

                    <form action="{{ route('tiposalidas.destroy', $tipoSalida) }}"
                          method="POST"
                          class="d-inline">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="btn btn-danger"
                                onclick="return confirm('¿Seguro que desea eliminar este registro?')">
                            <i class="bi bi-trash"></i> Eliminar
                        </button>
                    </form>

                </div>

            </div>

        </div>
    </div>

</main>

@endsection
