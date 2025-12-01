@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Listado de Usuarios</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Usuarios</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tabla -->
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Lista de Usuarios</h3>

                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm ms-auto">
                        <i class="fas fa-user-plus"></i> Nuevo Usuario
                    </a>
                </div>

                <div class="card-body p-0">

                    <table class="table table-bordered table-striped mb-0">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th width="170px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($users as $user)
                            <tr class="align-middle">
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->full_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->roles->isNotEmpty())
                                        <span class="badge bg-info">{{ $user->roles->first()->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Sin rol</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($user->estado == 'ACTIVO') bg-success
                                        @elseif($user->estado == 'NO ACTIVO') bg-warning
                                        @elseif($user->estado == 'PENDIENTE') bg-orange
                                        @else bg-danger @endif">
                                        {{ $user->estado ?? 'DESCONOCIDO' }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <button type="button"
                                        class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar"
                                        data-id="{{ $user->id }}"
                                        data-nombre="{{ $user->full_name }}">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </td>
                            </tr>

                        @empty
                            <tr><td colspan="6" class="text-center p-3">
                                No existen usuarios registrados.
                            </td></tr>
                        @endforelse
                        </tbody>
                    </table>

                </div>

                <div class="card-footer clearfix">
                    <div class="float-end">
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>¿Desea eliminar al usuario:</p>
                <h5 class="fw-bold text-danger" id="nombreUser"></h5>
                <p>Esta acción no se puede deshacer.</p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                <form id="formEliminar" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modalEliminar = document.getElementById('modalEliminar');

    modalEliminar.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        document.getElementById('nombreUser').textContent =
            button.getAttribute('data-nombre');

        document.getElementById('formEliminar').action =
            `/users/${button.getAttribute('data-id')}`;
    });
</script>
@endpush
