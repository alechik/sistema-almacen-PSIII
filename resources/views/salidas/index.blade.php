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

                                                <form action="{{ route('salidas.cambiarEstado', $s) }}"
                                                      method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="accion" value="confirmar">
                                                    <button class="btn btn-success btn-sm" title="Confirmar">
                                                        <i class="bi bi-check2-circle"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('salidas.cambiarEstado', $s) }}"
                                                      method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="accion" value="anular">
                                                    <button class="btn btn-danger btn-sm" title="Anular">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>

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

</main>
@endsection
