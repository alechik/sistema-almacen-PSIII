@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Listado de Pedidos</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Pedidos</li>
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

            <!-- Tabla Pedidos -->
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Pedidos</h3>

                    {{-- <a href="{{ route('pedidos.create') }}" class="btn btn-primary btn-sm ms-auto">
                        <i class="fas fa-plus"></i> Nuevo Pedido
                    </a> --}}
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Cod. Comprobante</th>
                                <th>Proveedor</th>
                                <th>Almacén</th>
                                <th>Administrador</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th width="160px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($pedidos as $pedido)
                            <tr class="align-middle text-center">

                                <td>{{ $pedido->id }}</td>
                                <td>{{ $pedido->codigo_comprobante }}</td>

                                @php
                                    $prov = collect($proveedores)->firstWhere('id', $pedido->proveedor_id);
                                @endphp

                                <td>{{ $prov['nombre'] ?? '—' }}</td>

                                <td>{{ $pedido->almacen->nombre ?? '—' }}</td>

                                <td>{{ $pedido->administrador->full_name ?? '—' }}</td>

                                <td>{{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}</td>
                                <td>
                                    @if ($pedido->estado == 0)
                                        <span class="badge bg-danger">CANCELADO</span>
                                    @elseif ($pedido->estado == 1)
                                        <span class="badge bg-primary">EMITIDO</span>
                                    @elseif ($pedido->estado == 2)
                                        <span class="badge bg-warning text-dark">CONFIRMADO</span>
                                    @elseif ($pedido->estado == 3)
                                        <span class="badge bg-success">TERMINADO</span>
                                    @else
                                        <span class="badge bg-secondary">ANULADO</span>
                                    @endif
                                </td>

                                <td class="text-center">

                                    <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @hasrole('propietario')
                                        <a href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endhasrole

                                    <button type="button"
                                        class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEliminar"
                                        data-id="{{ $pedido->id }}"
                                        data-nombre="Pedido #{{ $pedido->codigo_comprobante }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-3">
                                    No existen pedidos registrados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-end">
                        {{ $pedidos->links('pagination::bootstrap-5') }}
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
                <p>¿Desea eliminar el siguiente pedido?</p>
                <h5 class="fw-bold text-danger" id="nombrePedido"></h5>
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
        document.getElementById('nombrePedido').textContent =
            button.getAttribute('data-nombre');

        document.getElementById('formEliminar').action =
            `/pedidos/${button.getAttribute('data-id')}`;
    });
</script>
@endpush
