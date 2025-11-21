@extends('dashboard-layouts.header-footer')
@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Registrar Nuevo Almacén</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}">Almacenes</a></li>
                        <li class="breadcrumb-item active">Nuevo</li>
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
                <div class="alert alert-danger alert-dismissible fade show">
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

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Datos del Almacén</h3>
                    <a href="{{ route('almacenes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <form action="{{ route('almacenes.store') }}" method="POST">
                    @csrf

                    <div class="card-body">

                        <div class="row">

                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre" 
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre') }}" required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado *</label>
                                <select name="estado" 
                                        class="form-select @error('estado') is-invalid @enderror" 
                                        required>
                                    <option value="">-- Seleccione un estado --</option>

                                    <option value="ACTIVADO"  {{ old('estado') == 'ACTIVADO' ? 'selected' : '' }}>ACTIVADO</option>
                                    <option value="DESACTIVADO" {{ old('estado') == 'DESACTIVADO' ? 'selected' : '' }}>DESACTIVADO</option>
                                    <option value="CERRADO"  {{ old('estado') == 'CERRADO' ? 'selected' : '' }}>CERRADO</option>
                                </select>

                                @error('estado')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Ubicación -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Ubicación</label>
                                <textarea name="ubicacion" rows="2"
                                          class="form-control @error('ubicacion') is-invalid @enderror">{{ old('ubicacion') }}</textarea>
                                @error('ubicacion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Longitud -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Longitud</label>
                                <input type="text" name="longitud" 
                                       class="form-control @error('longitud') is-invalid @enderror"
                                       value="{{ old('longitud') }}">
                                @error('longitud')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Latitud -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Latitud</label>
                                <input type="text" name="latitud" 
                                       class="form-control @error('latitud') is-invalid @enderror"
                                       value="{{ old('latitud') }}">
                                @error('latitud')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="cellphone"
                                       class="form-control @error('cellphone') is-invalid @enderror"
                                       value="{{ old('cellphone') }}">
                                @error('cellphone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" rows="3"
                                          class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion') }}</textarea>
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Almacén
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>

</main>

@endsection
