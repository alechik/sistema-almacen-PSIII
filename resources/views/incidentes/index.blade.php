@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Monitoreo de Incidentes</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item active">Incidentes</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                Incidentes Reportados
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($incidentes->isEmpty())
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    No hay incidentes reportados en este momento.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Fecha Reporte</th>
                                                <th>Pedido</th>
                                                <th>Código Envío</th>
                                                <th>Tipo Incidente</th>
                                                <th>Transportista</th>
                                                <th>Acción</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($incidentes as $incidente)
                                                <tr>
                                                    <td>{{ $incidente->fecha_reporte->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        <strong>{{ $incidente->pedido->codigo_comprobante ?? 'N/A' }}</strong>
                                                    </td>
                                                    <td>{{ $incidente->envio_codigo }}</td>
                                                    <td>
                                                        <span class="badge bg-warning text-dark">
                                                            {{ $incidente->tipo_incidente }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $incidente->transportista_nombre ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($incidente->accion === 'cancelar')
                                                            <span class="badge bg-danger">Cancelado</span>
                                                        @else
                                                            <span class="badge bg-info">Continúa</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($incidente->estado === 'pendiente')
                                                            <span class="badge bg-warning">Pendiente</span>
                                                        @elseif($incidente->estado === 'en_proceso')
                                                            <span class="badge bg-primary">En Proceso</span>
                                                        @else
                                                            <span class="badge bg-success">Resuelto</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('incidentes.show', $incidente) }}" 
                                                           class="btn btn-sm btn-primary">
                                                            <i class="bi bi-eye"></i> Ver Detalles
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

