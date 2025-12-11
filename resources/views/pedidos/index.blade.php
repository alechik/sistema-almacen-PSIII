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

                                    <a title="Ver Pedido" href="{{ route('pedidos.show', $pedido) }}" class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @hasrole('propietario')
                                        @if ($pedido->estado==1)
                                            <button 
                                                title="Confirmar pedido"
                                                class="btn btn-success btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmarModal"
                                                data-id="{{ $pedido->id }}"
                                                data-codigo="{{ $pedido->codigo_comprobante }}"
                                                data-fecha="{{ $pedido->fecha }}"
                                                data-almacen="{{ $pedido->almacen->nombre }}"
                                                data-proveedor="{{ $prov['nombre'] ?? 'No definido' }}"
                                                data-detalles='@json($pedido->detalles)'>
                                                <i class="bi bi-bag-check"></i>
                                            </button>
                                            <a title="Editar Pedido" href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @endif

                                    @endhasrole

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

<!-- ========================= -->
<!--  MODAL CONFIRMAR PEDIDO   -->
<!-- ========================= -->
<div class="modal fade" id="confirmarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Confirmar Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <ul class="list-group mb-3">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-6"><strong>Código:</strong> <span id="codigoPedido"></span></div>
                            <div class="col-6"><strong>Fecha:</strong> <span id="fechaPedido"></span></div>
                        </div>
                    </li>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-6"><strong>Almacén:</strong> <span id="almacenPedido"></span></div>
                            <div class="col-6"><strong>Proveedor:</strong> <span id="proveedorPedido"></span></div>
                        </div>
                    </li>
                </ul>

                <h6 class="text-uppercase fw-bold">Detalle del Pedido</h6>

                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center" style="width:25%">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody id="tablaDetalles"></tbody>
                </table>

                <p class="text-muted small mt-2">Esta acción no se puede revertir.</p>

            </div>

            <div class="modal-footer">
                <form id="formConfirmar" method="POST">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success">Sí, Confirmar</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modalConfirmar = document.getElementById('confirmarModal');

    modalConfirmar.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;

        document.getElementById('codigoPedido').textContent = button.getAttribute('data-codigo');
        document.getElementById('fechaPedido').textContent = button.getAttribute('data-fecha');
        document.getElementById('almacenPedido').textContent = button.getAttribute('data-almacen');
        document.getElementById('proveedorPedido').textContent = button.getAttribute('data-proveedor');

        // Cambiar acción del formulario
        let url = "/pedidos/confirmar/" + button.getAttribute('data-id');
        document.getElementById('formConfirmar').action = url;

        // Cargar detalles
        let detalles = JSON.parse(button.getAttribute('data-detalles'));
        let tbody = document.getElementById('tablaDetalles');
        tbody.innerHTML = ""; 

        detalles.forEach(det => {
            tbody.innerHTML += `
                <tr>
                    <td>${det.producto.nombre}</td>
                    <td class="text-center">${det.cantidad}</td>
                </tr>
            `;
        });

    });
</script>
@endpush
