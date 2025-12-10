@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-person-badge text-primary"></i> Mis Pedidos a Planta
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('envios-planta.index') }}">Envíos Planta</a></li>
                        <li class="breadcrumb-item active">Mis Pedidos</li>
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
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('warning') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-primary">
                        <div class="inner">
                            <h3>{{ $estadisticas['total'] }}</h3>
                            <p>Total Pedidos</p>
                        </div>
                        <div class="icon"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-secondary">
                        <div class="inner">
                            <h3>{{ $estadisticas['pendientes'] }}</h3>
                            <p>Pendientes</p>
                        </div>
                        <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3>{{ $estadisticas['en_transito'] }}</h3>
                            <p>En Camino</p>
                        </div>
                        <div class="icon"><i class="bi bi-truck"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3>{{ $estadisticas['entregados'] }}</h3>
                            <p>Entregados</p>
                        </div>
                        <div class="icon"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="mb-4">
                <a href="{{ route('envios-planta.pedido.create') }}" class="btn btn-success btn-lg">
                    <i class="bi bi-plus-circle"></i> Nuevo Pedido a Planta
                </a>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('envios-planta.mis-envios') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="asignado" {{ request('estado') == 'asignado' ? 'selected' : '' }}>Asignado</option>
                                <option value="en_transito" {{ request('estado') == 'en_transito' ? 'selected' : '' }}>En Tránsito</option>
                                <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <a href="{{ route('envios-planta.mis-envios') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Pedidos -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Mis Pedidos
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Almacén Destino</th>
                                <th>Fecha Solicitada</th>
                                <th>Productos</th>
                                <th>Estado</th>
                                <th>Transportista</th>
                                <th class="text-center" width="180px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($envios as $envio)
                                <tr>
                                    <td>
                                        <strong>{{ $envio->codigo }}</strong>
                                        @if($envio->tieneIncidentesPendientes())
                                            <span class="badge bg-danger ms-1" title="Tiene incidentes">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $envio->almacen->nombre ?? '-' }}</td>
                                    <td>
                                        {{ $envio->fecha_estimada_entrega?->format('d/m/Y') ?? '-' }}
                                        @if($envio->hora_estimada)
                                            <small class="text-muted">{{ $envio->hora_estimada }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $envio->total_cantidad }} unid.</span>
                                        <small class="text-muted d-block">{{ number_format($envio->total_peso, 2) }} kg</small>
                                    </td>
                                    <td>
                                        <span class="fs-5">{{ $envio->estado_icono }}</span>
                                        {!! $envio->estado_badge !!}
                                    </td>
                                    <td>
                                        @if($envio->transportista_nombre)
                                            <i class="bi bi-person"></i> {{ $envio->transportista_nombre }}
                                            @if($envio->vehiculo_placa)
                                                <br><small class="text-muted">
                                                    <i class="bi bi-truck"></i> {{ $envio->vehiculo_placa }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">Sin asignar</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('envios-planta.show', $envio) }}" 
                                           class="btn btn-info btn-sm" title="Ver Detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($envio->estaEnTransito())
                                            <a href="{{ route('envios-planta.monitoreo', $envio) }}" 
                                               class="btn btn-warning btn-sm" title="Monitorear">
                                                <i class="bi bi-geo-alt"></i>
                                            </a>
                                        @endif
                                        @if($envio->estaEntregado())
                                            <a href="{{ route('envios-planta.nota-recepcion', $envio) }}" 
                                               class="btn btn-success btn-sm" title="Nota de Recepción">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                        <p class="text-muted mb-3">No has realizado ningún pedido a planta aún.</p>
                                        <a href="{{ route('envios-planta.pedido.create') }}" class="btn btn-success">
                                            <i class="bi bi-plus-circle"></i> Crear mi primer pedido
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $envios->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>

</main>

@endsection

