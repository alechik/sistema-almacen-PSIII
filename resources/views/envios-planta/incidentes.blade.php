@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">
                        <i class="bi bi-exclamation-triangle text-danger"></i> Incidentes de Envíos
                    </h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('envios-planta.index') }}">Envíos Planta</a></li>
                        <li class="breadcrumb-item active">Incidentes</li>
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

            <!-- Estadísticas -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-danger">
                        <div class="inner">
                            <h3>{{ $estadisticas['pendientes'] }}</h3>
                            <p>Pendientes</p>
                        </div>
                        <div class="icon"><i class="bi bi-exclamation-circle"></i></div>
                        <a href="{{ route('envios-planta.incidentes', ['estado' => 'pendiente']) }}" class="small-box-footer">
                            Ver pendientes <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3>{{ $estadisticas['en_proceso'] }}</h3>
                            <p>En Proceso</p>
                        </div>
                        <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                        <a href="{{ route('envios-planta.incidentes', ['estado' => 'en_proceso']) }}" class="small-box-footer">
                            Ver en proceso <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3>{{ $estadisticas['resueltos'] }}</h3>
                            <p>Resueltos</p>
                        </div>
                        <div class="icon"><i class="bi bi-check-circle"></i></div>
                        <a href="{{ route('envios-planta.incidentes', ['estado' => 'resuelto']) }}" class="small-box-footer">
                            Ver resueltos <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-secondary">
                        <div class="inner">
                            <h3>{{ $estadisticas['total'] }}</h3>
                            <p>Total</p>
                        </div>
                        <div class="icon"><i class="bi bi-list-ul"></i></div>
                        <a href="{{ route('envios-planta.incidentes') }}" class="small-box-footer">
                            Ver todos <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('envios-planta.incidentes') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                <option value="resuelto" {{ request('estado') == 'resuelto' ? 'selected' : '' }}>Resuelto</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Filtrar
                                </button>
                                <a href="{{ route('envios-planta.incidentes') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-lg"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Incidentes -->
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Listado de Incidentes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Envío</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Fecha Reporte</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($incidentes as $incidente)
                                <tr class="{{ $incidente->estado == 'pendiente' ? 'table-danger' : ($incidente->estado == 'en_proceso' ? 'table-warning' : '') }}">
                                    <td>
                                        <strong>#{{ $incidente->id }}</strong>
                                        @if(!$incidente->visto)
                                            <span class="badge bg-primary">Nuevo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('envios-planta.show', $incidente->envioPlanta) }}">
                                            {{ $incidente->envioPlanta->codigo ?? 'N/A' }}
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            {{ $incidente->envioPlanta->almacen->nombre ?? 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="fs-5">{{ $incidente->tipo_icono }}</span>
                                        {{ $incidente->tipo_texto }}
                                    </td>
                                    <td>{{ Str::limit($incidente->descripcion, 50) }}</td>
                                    <td>{!! $incidente->estado_badge !!}</td>
                                    <td>{{ $incidente->fecha_reporte?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('envios-planta.incidente-show', $incidente) }}" 
                                           class="btn btn-info btn-sm">
                                            <i class="bi bi-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-check-circle text-success fs-1"></i>
                                        <p class="text-muted mt-2 mb-0">No hay incidentes registrados</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $incidentes->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>

        </div>
    </div>

</main>

@endsection

