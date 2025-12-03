@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Editar Unidad de Medida</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('unidad-medidas.index') }}">Unidades de Medida</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Errores -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Hay errores en el formulario:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">Actualizar datos de la Unidad de Medida</h3>
                </div>

                <div class="card-body">

                    <form action="{{ route('unidad-medidas.update', $unidadMedida) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Código -->
                        <div class="mb-3">
                            <label for="cod_unidad_medida" class="form-label">Código *</label>
                            <input type="text"
                                name="cod_unidad_medida"
                                id="cod_unidad_medida"
                                class="form-control @error('cod_unidad_medida') is-invalid @enderror"
                                value="{{ old('cod_unidad_medida', $unidadMedida->cod_unidad_medida) }}"
                                placeholder="Ej: KG">

                            @error('cod_unidad_medida')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <input type="text"
                                name="descripcion"
                                id="descripcion"
                                class="form-control @error('descripcion') is-invalid @enderror"
                                value="{{ old('descripcion', $unidadMedida->descripcion) }}"
                                placeholder="Ej: Kilogramos">

                            @error('descripcion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('unidad-medidas.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancelar
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Actualizar
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>

</main>

@endsection
