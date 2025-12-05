@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Listado de Ingresos</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item active">Ingresos</li>
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

            <!-- Tabla Ingresos -->
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Ingresos</h3>

                    {{-- <a href="{{ route('ingresos.create') }}" class="btn btn-primary btn-sm ms-auto">
                        <i class="fas fa-plus"></i> Nuevo Ingreso
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
                                <th width="140px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse ($ingresos as $ingreso)
                            <tr class="align-middle text-center">

                                <td>{{ $ingreso->id }}</td>

                                <td>{{ $ingreso->codigo_comprobante }}</td>

                                @php
                                    $proveedor = collect($proveedores)->firstWhere('id', $ingreso->proveedor_id);
                                @endphp

                                <td>{{ $proveedor['nombre'] ?? '—' }}</td>

                                <td>{{ $ingreso->almacen->nombre ?? '—' }}</td>

                                <td>{{ $ingreso->administrador->full_name ?? '—' }}</td>

                                <td>{{ \Carbon\Carbon::parse($ingreso->fecha)->format('d/m/Y') }}</td>

                                <td>
                                    @if ($ingreso->estado == 0)
                                        <span class="badge bg-danger">ANULADO</span>
                                    @elseif ($ingreso->estado == 1)
                                        <span class="badge bg-primary">EMITIDO</span>
                                    @elseif ($ingreso->estado == 2)
                                        <span class="badge bg-warning text-dark">CONFIRMADO</span>
                                    @elseif ($ingreso->estado == 3)
                                        <span class="badge bg-success">TERMINADO</span>
                                    @else
                                        <span class="badge bg-secondary">SIN ESTADO</span>
                                    @endif
                                    <br>
                                    @hasrole('propietario|administrador')
                                        @if ($ingreso->estado == 1)

                                            <!-- Confirmar -->
                                            <form action="{{ route('ingresos.cambiarEstado', $ingreso) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="accion" value="confirmar">
                                                <button title="Confirmar" class="btn btn-success btn-sm">
                                                    <i class="bi bi-check2-circle"></i>
                                                </button>
                                            </form>

                                            <!-- Anular -->
                                            <form action="{{ route('ingresos.cambiarEstado', $ingreso) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="accion" value="anular">
                                                <button title="Anular" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>

                                        @endif
                                    @endhasrole

                                </td>

                                <td class="text-center">

                                    <!-- Ver ingreso -->
                                    <a title="Ver Ingreso" href="{{ route('ingresos.show', $ingreso) }}" 
                                        class="btn btn-info btn-sm">
                                        <i class="bi bi-eye"></i>
                                    </a>



                                    <!-- Editar ingreso solo para propietario -->
                                    @hasrole('propietario|administrador')
                                    @if ($ingreso->estado==1)
                                        <a title="Editar Ingreso" href="{{ route('ingresos.edit', $ingreso) }}" 
                                            class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        
                                    @endif
                                    @endhasrole

                                </td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-3">
                                    No existen ingresos registrados.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer clearfix">
                    <div class="float-end">
                        {{ $ingresos->links('pagination::bootstrap-5') }}
                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

@endsection
