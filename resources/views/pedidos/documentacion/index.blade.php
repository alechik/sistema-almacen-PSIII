@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Documentación de Pedidos</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pedidos.index') }}">Pedidos</a></li>
                        <li class="breadcrumb-item active">Documentación</li>
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
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                                Documentación de Pedidos Entregados
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($pedidosConDocumentos->isEmpty())
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    No hay pedidos con documentación disponible en este momento.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Código Pedido</th>
                                                <th>Almacén</th>
                                                <th>Fecha Pedido</th>
                                                <th>Código Envío</th>
                                                <th>Transportista</th>
                                                <th>Fecha Entrega</th>
                                                <th>Documentos</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pedidosConDocumentos as $pedido)
                                            <tr>
                                                <td>
                                                    <strong>{{ $pedido->codigo_comprobante }}</strong>
                                                    <br>
                                                    <small class="text-muted">ID: {{ $pedido->id }}</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $almacen = \App\Models\Almacen::find(\App\Models\Pedido::find($pedido->id)->almacen_id ?? null);
                                                    @endphp
                                                    {{ $almacen->nombre ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($pedido->fecha)->format('d/m/Y') }}
                                                </td>
                                                <td>
                                                    <code>{{ $pedido->envio_codigo }}</code>
                                                </td>
                                                <td>{{ $pedido->transportista_nombre ?? 'N/A' }}</td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($pedido->fecha_entrega)->format('d/m/Y H:i') }}
                                                </td>
                                                <td>
                                                    @php
                                                        $documentos = $pedido->documentos ?? [];
                                                        $count = count(array_filter($documentos));
                                                    @endphp
                                                    <span class="badge bg-info">{{ $count }} documento(s)</span>
                                                    <br>
                                                    @if(isset($documentos['propuesta_vehiculos']))
                                                        <small><i class="bi bi-check-circle-fill text-success"></i> Propuesta Vehículos</small><br>
                                                    @endif
                                                    @if(isset($documentos['nota_entrega']))
                                                        <small><i class="bi bi-check-circle-fill text-success"></i> Nota de Entrega</small><br>
                                                    @endif
                                                    @if(isset($documentos['trazabilidad_completa']))
                                                        <small><i class="bi bi-check-circle-fill text-success"></i> Trazabilidad Completa</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('pedidos.documentacion.show', $pedido->id) }}" 
                                                       class="btn btn-primary btn-sm" 
                                                       title="Ver Documentación">
                                                        <i class="bi bi-eye"></i> Ver
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

