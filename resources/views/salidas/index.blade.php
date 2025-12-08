@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Listado de Salidas</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Salidas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="app-content">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Salidas</h3>

                    @hasrole('administrador|propietario')
                        <a href="{{ route('salidas.create') }}" class="btn btn-primary btn-sm ms-auto">
                            <i class="fas fa-plus"></i> Nueva Salida
                        </a>
                    @endhasrole
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="text-center">
                            <tr>
                                <th>ID</th>
                                <th>Cod. Comp.</th>
                                <th>Punto Venta</th>
                                <th>Almacén</th>
                                <th>Operador</th>
                                <th>Transportista</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th width="140px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($salidas as $s)
                                <tr class="text-center align-middle">

                                    <td>{{ $s->id }}</td>
                                    <td>{{ $s->codigo_comprobante }}</td>

                                    <td>{{ $s->punto_venta_id }}</td>

                                    <td>{{ $s->almacen->nombre ?? '—' }}</td>
                                    <td>{{ $s->operador->full_name ?? '—' }}</td>
                                    <td>{{ $s->transportista->full_name ?? '—' }}</td>

                                    <td>{{ \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') }}</td>

                                    <td>
                                        @if ($s->estado == 0)
                                            <span class="badge bg-danger">CANCELADO</span>
                                        @elseif ($s->estado == 1)
                                            <span class="badge bg-primary">EMITIDO</span>
                                        @elseif ($s->estado == 2)
                                            <span class="badge bg-warning text-dark">CONFIRMADO</span>
                                        @elseif ($s->estado == 3)
                                            <span class="badge bg-success">TERMINADO</span>
                                        @elseif ($s->estado == 4)
                                            <span class="badge bg-danger">ANULADO</span>
                                        @endif
                                        <br>
                                        @hasrole('administrador|propietario')
                                            @if ($s->estado == 1)

                                                <!-- Botón abrir modal Confirmar -->
                                                <button
                                                    class="btn btn-success btn-sm"
                                                    title="Confirmar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalConfirmar"
                                                    data-id="{{ $s->id }}"
                                                >
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>

                                                <!-- Botón abrir modal Anular -->
                                                <button
                                                    class="btn btn-danger btn-sm"
                                                    title="Anular"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalAnular"
                                                    data-id="{{ $s->id }}"
                                                >
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            @endif
                                        @endhasrole

                                    </td>

                                    <td>
                                        <a href="{{ route('salidas.show', $s) }}"
                                           class="btn btn-info btn-sm" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @hasrole('administrador|propietario')
                                        @if ($s->estado == 1)
                                            <a href="{{ route('salidas.edit', $s) }}"
                                               class="btn btn-warning btn-sm" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @endif
                                        @endhasrole
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="9" class="text-center p-3">No existen salidas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-end">
                        {{ $salidas->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>

        </div>
    </div>

<!-- Modal Confirmar -->
<div class="modal fade" id="modalConfirmar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Confirmar Salida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        ¿Está seguro de confirmar esta salida? <br>
        <strong>Esta acción no se podrá revertir.</strong>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

        <form id="formConfirmar" method="POST" class="d-inline">
            @csrf
            @method('PUT')
            <input type="hidden" name="accion" value="confirmar">
            <button class="btn btn-success">Confirmar</button>
        </form>
      </div>

    </div>
  </div>
</div>
<!-- Modal Anular -->
<div class="modal fade" id="modalAnular" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Anular Salida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        ¿Desea anular esta salida?<br>
        <strong>Una vez anulada, no se podrá revertir.</strong>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

        <form id="formAnular" method="POST" class="d-inline">
            @csrf
            @method('PUT')
            <input type="hidden" name="accion" value="anular">
            <button class="btn btn-danger">Anular</button>
        </form>
      </div>

    </div>
  </div>
</div>

</main>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Modal Confirmar
    var modalConfirmar = document.getElementById('modalConfirmar');
    modalConfirmar.addEventListener('show.bs.modal', function (event) {
        var boton = event.relatedTarget;
        var id = boton.getAttribute('data-id');
        document.getElementById('formConfirmar').action =
            "/salidas/" + id + "/cambiar-estado";
    });

    // Modal Anular
    var modalAnular = document.getElementById('modalAnular');
    modalAnular.addEventListener('show.bs.modal', function (event) {
        var boton = event.relatedTarget;
        var id = boton.getAttribute('data-id');
        document.getElementById('formAnular').action =
            "/salidas/" + id + "/cambiar-estado";
    });

});
</script>
@endpush

@endsection
