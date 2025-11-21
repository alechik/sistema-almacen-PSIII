@extends('dashboard-layouts.header-footer')
@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalle del Almacén</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}">Almacenes</a></li>
                        <li class="breadcrumb-item active">Ver Detalle</li>
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
                    <h3 class="card-title">Información del Almacén</h3>

                    <a href="{{ route('almacenes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">

                    <div class="row">

                        <!-- Nombre -->
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Nombre:</label>
                            <p>{{ $almacen->nombre }}</p>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Estado:</label>
                            <p>
                                @php
                                    $badgeClass = match($almacen->estado) {
                                        'ACTIVADO' => 'badge bg-success',
                                        'DESACTIVADO' => 'badge bg-warning',
                                        'CERRADO' => 'badge bg-danger',
                                        default => 'badge bg-secondary'
                                    };
                                @endphp

                                <span class="{{ $badgeClass }}">
                                    {{ $almacen->estado }}
                                </span>
                            </p>
                        </div>

                        <!-- Ubicación -->
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Ubicación:</label>
                            <p>{{ $almacen->ubicacion ?? '—' }}</p>
                        </div>

                        <!-- Longitud -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Longitud:</label>
                            <p>{{ $almacen->longitud ?? '—' }}</p>
                        </div>

                        <!-- Latitud -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Latitud:</label>
                            <p>{{ $almacen->latitud ?? '—' }}</p>
                        </div>

                        <!-- Celular -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Celular:</label>
                            <p>{{ $almacen->cellphone ?? '—' }}</p>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Email:</label>
                            <p>{{ $almacen->email ?? '—' }}</p>
                        </div>

                        <!-- Descripción -->
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Descripción:</label>
                            <p>{{ $almacen->descripcion ?? '—' }}</p>
                        </div>

                        <!-- Usuario Creador -->
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Registrado por:</label>
                            <p>{{ $almacen->user->name ?? '—' }}</p>
                        </div>

                    </div>
                </div>

                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('almacenes.edit', $almacen->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>

                    <form action="{{ route('almacenes.destroy', $almacen->id) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar este almacén?')">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>

</main>

@endsection
