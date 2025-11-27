@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Listado de Almacenes</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Almacenes</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Mensajes -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

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

            <!-- Tarjeta principal -->
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Lista de Almacenes</h3>
                    <a href="{{ route('almacenes.create') }}" class="btn btn-primary btn-sm ms-auto">
                        <i class="fas fa-plus"></i> Nuevo Almacén
                    </a>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Ubicación</th>
                                <th>Email</th>
                                <th>Estado</th>
                                <th width="150px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($almacenes as $almacen)
                            <tr class="align-middle">
                                <td>{{ $almacen->id }}</td>
                                <td>{{ $almacen->nombre }}</td>
                                <td>{{ $almacen->ubicacion }}</td>
                                <td>{{ $almacen->email }}</td>
                                <td>{{ $almacen->estado }}</td>

                                <td class="text-center">

                                    <a href="{{ route('almacenes.show', $almacen) }}" 
                                       class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('almacenes.edit', $almacen->id) }}" 
                                       class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <form action="{{ route('almacenes.destroy', $almacen) }}"
                                          method="POST"
                                          style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Eliminar este almacén?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-3">
                                    No existen almacenes registrados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>

                <!-- Paginación -->
                <div class="card-footer clearfix">
                    <div class="float-end">
                        {{ $almacenes->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

@endsection
