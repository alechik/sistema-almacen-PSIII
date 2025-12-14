@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Documentación del Pedido</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pedidos.index') }}">Pedidos</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pedidos.documentacion') }}">Documentación</a></li>
                        <li class="breadcrumb-item active">{{ $pedido->codigo_comprobante }}</li>
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
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-file-earmark-pdf-fill me-2"></i>
                                Documentación del Pedido: {{ $pedido->codigo_comprobante }}
                            </h5>
                            <a href="{{ route('pedidos.documentacion') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                        </div>
                        <div class="card-body">
                            <!-- Información del Pedido -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-info"><i class="bi bi-cart"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Código de Pedido</span>
                                            <span class="info-box-number">{{ $pedido->codigo_comprobante }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-success"><i class="bi bi-building"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Almacén</span>
                                            <span class="info-box-number">{{ $pedido->almacen->nombre ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box mb-3">
                                        <span class="info-box-icon bg-warning"><i class="bi bi-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Fecha del Pedido</span>
                                            <span class="info-box-number">{{ $pedido->fecha->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($documentaciones->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    No se encontró documentación para este pedido.
                                </div>
                            @else
                                @foreach($documentaciones as $index => $documentacion)
                                <div class="card card-outline card-primary mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="bi bi-file-pdf me-1"></i>
                                            Documentación de Entrega #{{ $index + 1 }}
                                        </h5>
                                        <div class="card-tools">
                                            <small class="text-muted">
                                                Recibido: {{ \Carbon\Carbon::parse($documentacion->created_at)->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <strong>Código Envío:</strong><br>
                                                <code>{{ $documentacion->envio_codigo }}</code>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Transportista:</strong><br>
                                                {{ $documentacion->transportista_nombre ?? 'N/A' }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Fecha de Entrega:</strong><br>
                                                {{ \Carbon\Carbon::parse($documentacion->fecha_entrega)->format('d/m/Y H:i') }}
                                            </div>
                                        </div>

                                        <hr>

                                        <h6 class="mb-3">
                                            <i class="bi bi-folder-open me-1"></i>
                                            Documentos Disponibles
                                        </h6>

                                        <div class="row">
                                            @php
                                                $documentos = $documentacion->documentos ?? [];
                                            @endphp
                                            
                                            @if(isset($documentos['propuesta_vehiculos']))
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-primary">
                                                    <div class="card-body text-center">
                                                        <i class="bi bi-truck fa-3x text-primary mb-3"></i>
                                                        <h6 class="card-title">Propuesta de Vehículos</h6>
                                                        <p class="card-text text-muted small">Documento con la propuesta de vehículos para el envío</p>
                                                        <a href="{{ route('pedidos.documentacion.descargar', ['pedido' => $pedido->id, 'tipo' => 'propuesta_vehiculos_path']) }}" 
                                                           target="_blank"
                                                           class="btn btn-primary btn-sm">
                                                            <i class="bi bi-eye"></i> Ver PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            @if(isset($documentos['nota_entrega']))
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-success">
                                                    <div class="card-body text-center">
                                                        <i class="bi bi-clipboard-check fa-3x text-success mb-3"></i>
                                                        <h6 class="card-title">Nota de Entrega</h6>
                                                        <p class="card-text text-muted small">Documento con checklist y firma del transportista</p>
                                                        <a href="{{ route('pedidos.documentacion.descargar', ['pedido' => $pedido->id, 'tipo' => 'nota_entrega_path']) }}" 
                                                           target="_blank"
                                                           class="btn btn-success btn-sm">
                                                            <i class="bi bi-eye"></i> Ver PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            @if(isset($documentos['trazabilidad_completa']))
                                            <div class="col-md-4 mb-3">
                                                <div class="card border-info">
                                                    <div class="card-body text-center">
                                                        <i class="bi bi-diagram-3 fa-3x text-info mb-3"></i>
                                                        <h6 class="card-title">Trazabilidad Completa</h6>
                                                        <p class="card-text text-muted small">Documento con todas las fechas y eventos del envío</p>
                                                        <a href="{{ route('pedidos.documentacion.descargar', ['pedido' => $pedido->id, 'tipo' => 'trazabilidad_completa_path']) }}" 
                                                           target="_blank"
                                                           class="btn btn-info btn-sm">
                                                            <i class="bi bi-eye"></i> Ver PDF
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>

                                        @if(empty($documentos))
                                            <div class="alert alert-warning">
                                                <i class="bi bi-exclamation-triangle"></i>
                                                No hay documentos disponibles para esta entrega.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

