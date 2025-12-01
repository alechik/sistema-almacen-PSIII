@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">
    
    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between">
            <h3 class="mb-0">Detalles del Usuario</h3>
            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Usuarios</a></li>
                <li class="breadcrumb-item active">Ver Usuario</li>
            </ol>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Información General</h3>
                </div>

                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Nombre Completo:</strong>
                            <p>{{ $user->full_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Nombre de Usuario:</strong>
                            <p>{{ $user->name }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Correo Electrónico:</strong>
                            <p>{{ $user->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Teléfono:</strong>
                            <p>{{ $user->phone_number ?? 'Sin especificar' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Empresa:</strong>
                            <p>{{ $user->company ?? 'Sin asignar' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Rol:</strong>
                            <span class="badge bg-success">
                                {{ $roles->first() }}
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Estado:</strong>
                            <span class="badge {{ $user->estado == 'ACTIVO' ? 'bg-primary' : 'bg-danger' }}">
                                {{ $user->estado }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>

                        @can('update', $user)
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning text-white">
                            <i class="bi bi-pencil-square"></i> Editar Usuario
                        </a>
                        @endcan
                    </div>

                </div>
            </div>

        </div>
    </div>

</main>

@endsection
