@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Editar Asignación</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('almacen-users.index') }}">Asignación de Usuarios</a></li>
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

            @if (session('error'))
                <div class="alert alert-warning alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">Editar Asignación de Usuario a Almacén</h3>
                </div>

                <div class="card-body">

                    <form action="{{ route('almacen-users.update', $almacenUser->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Usuario -->
                        <div class="mb-3">
                            <label class="form-label">Usuario *</label>
                            <select name="user_id" class="form-control @error('user_id') is-invalid @enderror">
                                <option value="">Seleccione un usuario</option>

                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}"
                                        {{ old('user_id', $almacenUser->user_id) == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('user_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Almacén -->
                        <div class="mb-3">
                            <label class="form-label">Almacén *</label>
                            <select name="almacen_id" class="form-control @error('almacen_id') is-invalid @enderror">
                                <option value="">Seleccione un almacén</option>

                                @foreach ($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}"
                                        {{ old('almacen_id', $almacenUser->almacen_id) == $almacen->id ? 'selected' : '' }}>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            @error('almacen_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('almacen-users.index') }}" class="btn btn-secondary">
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
