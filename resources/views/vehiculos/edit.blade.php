@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Editar Vehículo</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('vehiculos.index') }}">Vehículos</a></li>
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
                    <h3 class="card-title">Editar Vehículo</h3>
                </div>

                <div class="card-body">

                    <form action="{{ route('vehiculos.update', $vehiculo->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Placa -->
                        <div class="mb-3">
                            <label for="placa_identificacion" class="form-label">Placa de Identificación *</label>
                            <input type="text"
                                   name="placa_identificacion"
                                   id="placa_identificacion"
                                   class="form-control @error('placa_identificacion') is-invalid @enderror"
                                   value="{{ old('placa_identificacion', $vehiculo->placa_identificacion) }}"
                                   placeholder="Ej: 123-ABC">

                            @error('placa_identificacion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Marca / Modelo -->
                        <div class="mb-3">
                            <label for="marca_modelo" class="form-label">Marca / Modelo *</label>
                            <input type="text"
                                   name="marca_modelo"
                                   id="marca_modelo"
                                   class="form-control @error('marca_modelo') is-invalid @enderror"
                                   value="{{ old('marca_modelo', $vehiculo->marca_modelo) }}"
                                   placeholder="Ej: Toyota Hilux">

                            @error('marca_modelo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Año -->
                        <div class="mb-3">
                            <label for="anio" class="form-label">Año</label>
                            <input type="text"
                                   name="anio"
                                   id="anio"
                                   class="form-control @error('anio') is-invalid @enderror"
                                   value="{{ old('anio', $vehiculo->anio) }}"
                                   placeholder="Ej: 2020">

                            @error('anio')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('vehiculos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

</main>

@endsection
