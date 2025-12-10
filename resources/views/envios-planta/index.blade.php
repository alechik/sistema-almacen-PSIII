@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-truck text-primary"></i> Envíos desde Planta
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Envíos Planta</li>
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

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-info">
                        <div class="inner">
                            <h3>{{ $estadisticas['total'] }}</h3>
                            <p>Total Envíos</p>
                        </div>
                        <div class="icon"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3>{{ $estadisticas['en_transito'] }}</h3>
                            <p>En Tránsito</p>
                        </div>
                        <div class="icon"><i class="bi bi-truck"></i></div>
                        <a href="{{ route('envios-planta.index', ['estado' => 'en_transito']) }}" class="small-box-footer">
                            Ver en tránsito <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3>{{ $estadisticas['entregados'] }}</h3>
                            <p>Entregados</p>
                        </div>
                        <div class="icon"><i class="bi bi-check-circle"></i></div>
                        <a href="{{ route('envios-planta.index', ['estado' => 'entregado']) }}" class="small-box-footer">
                            Ver entregados <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-danger">
                        <div class="inner">
                            <h3>{{ $estadisticas['incidentes'] }}</h3>
                            <p>Incidentes Pendientes</p>
                        </div>
                        <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
                        <a href="{{ route('envios-planta.incidentes') }}" class="small-box-footer">
                            Ver incidentes <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('envios-planta.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="asignado" {{ request('estado') == 'asignado' ? 'selected' : '' }}>Asignado</option>
                                <option value="en_transito" {{ request('estado') == 'en_transito' ? 'selected' : '' }}>En Tránsito</option>
                                <option value="entregado" {{ request('estado') == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Almacén</label>
                            <select name="almacen_id" class="form-select">
                                <option value="">Todos</option>
                                @foreach($almacenes as $almacen)
                                    <option value="{{ $almacen->id }}" {{ request('almacen_id') == $almacen->id ? 'selected' : '' }}>
                                        {{ $almacen->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Buscar</label>
                            <input type="text" name="buscar" class="form-control" 
                                   placeholder="Código, transportista, placa..." 
                                   value="{{ request('buscar') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Filtrar
                            </button>
                            <a href="{{ route('envios-planta.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de Envíos -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        <i class="bi bi-list-ul"></i> Listado de Envíos
                    </h3>
                    <div>
                        <a href="{{ route('envios-planta.dashboard') }}" class="btn btn-info btn-sm">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <form action="{{ route('envios-planta.sincronizar') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-repeat"></i> Sincronizar
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Almacén Destino</th>
                                <th>Transportista</th>
                                <th>Vehículo</th>
                                <th>Fecha Estimada</th>
                                <th>Estado</th>
                                <th class="text-center" width="180px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($envios as $envio)
                                <tr class="{{ !$envio->visto ? 'table-info' : '' }}">
                                    <td>
                                        <strong>{{ $envio->codigo }}</strong>
                                        @if(!$envio->visto)
                                            <span class="badge bg-primary ms-1">Nuevo</span>
                                        @endif
                                        @if($envio->tieneIncidentesPendientes())
                                            <span class="badge bg-danger ms-1">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $envio->almacen->nombre ?? '-' }}</td>
                                    <td>{{ $envio->transportista_nombre ?? '-' }}</td>
                                    <td>{{ $envio->vehiculo_placa ?? '-' }}</td>
                                    <td>
                                        @if($envio->fecha_estimada_entrega)
                                            {{ $envio->fecha_estimada_entrega->format('d/m/Y') }}
                                            @if($envio->hora_estimada)
                                                <small class="text-muted">{{ $envio->hora_estimada }}</small>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{!! $envio->estado_badge !!}</td>
                                    <td class="text-center">
                                        <a href="{{ route('envios-planta.show', $envio) }}" 
                                           class="btn btn-info btn-sm" title="Ver detalle">
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
                                    <td colspan="7" class="text-center p-4">
                                        <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                        No hay envíos registrados.
                                        <br>
                                        <small class="text-muted">Los envíos aparecerán cuando Planta los cree y asigne.</small>
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

