@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalle del Tipo de Ingreso</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('tipoingresos.index') }}">Tipos de Ingreso</a></li>
                        <li class="breadcrumb-item active">Detalle</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">Información del Tipo de Ingreso</h3>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <label class="fw-bold">ID:</label>
                        <p>{{ $tipoIngreso->id }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Nombre:</label>
                        <p>{{ $tipoIngreso->nombre }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Descripción:</label>
                        <p>{{ $tipoIngreso->descripcion ?? 'Sin descripción' }}</p>
                    </div>

                </div>

                <div class="card-footer d-flex justify-content-between">

                    <a href="{{ route('tipoingresos.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>

                    <div>
                        <a href="{{ route('tipoingresos.edit', $tipoIngreso) }}" class="btn btn-warning">
                            <i class="bi bi-pencil-square"></i> Editar
                        </a>

                        <!-- Botón eliminar (abre modal general de index si lo deseas) -->
                        <form action="{{ route('tipoingresos.destroy', $tipoIngreso) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"
                                onclick="return confirm('¿Está seguro de eliminar este tipo de ingreso?')">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </form>

                    </div>

                </div>

            </div>

        </div>
    </div>

</main>

@endsection
