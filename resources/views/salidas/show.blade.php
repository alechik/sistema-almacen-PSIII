@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Detalle de la Salida</h3>

            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('salidas.index') }}">Salidas</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </div>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <!-- DATOS GENERALES -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5 class="mb-0">Datos Generales de la Salida</h5>

                    <button type="button" class="btn btn-danger btn-sm ms-auto"
                        data-bs-toggle="modal" data-bs-target="#pdfModal">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i>
                        PDF
                    </button>
                </div>

                <div class="card-body row">

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Código Comprobante:</label>
                        <p>{{ $salida->codigo_comprobante }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Fecha:</label>
                        <p>{{ $salida->fecha }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Fecha Mínima:</label>
                        <p>{{ $salida->fecha_min }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Fecha Máxima:</label>
                        <p>{{ $salida->fecha_max }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Estado:</label>
                        <p>
                            @switch($salida->estado)
                                @case(0) <span class="badge bg-secondary">Cancelado</span> @break
                                @case(1) <span class="badge bg-primary">Emitido</span> @break
                                @case(2) <span class="badge bg-success">Confirmado</span> @break
                                @case(3) <span class="badge bg-info">Terminado</span> @break
                                @case(4) <span class="badge bg-danger">Anulado</span> @break
                            @endswitch
                        </p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Almacén:</label>
                        <p>{{ $salida->almacen->nombre }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Operador:</label>
                        <p>{{ $salida->operador->full_name }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Transportista:</label>
                        <p>{{ $salida->transportista->full_name }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Vehículo:</label>
                        <p>{{ $salida->vehiculo?->placa_identificacion ?? 'No asignado' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Tipo de Salida:</label>
                        <p>{{ $salida->tipoSalida->nombre }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Punto de Venta:</label>
                        <p>{{ $puntoVentaNombre }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Nota de Venta:</label>
                        <p>{{ $salida->nota_venta_id }}</p>
                    </div>

                </div>
            </div>

            <!-- DETALLE -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Detalle de la Salida</h5>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio (Bs)</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($salida->detalles as $d)
                                <tr>
                                    <td>{{ $d->producto->nombre }}</td>
                                    <td>{{ $d->cant_salida }}</td>
                                    <td>{{ number_format($d->precio, 4) }}</td>
                                    <td>{{ number_format($d->cant_salida * $d->precio, 2) }} Bs</td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

                <div class="card-footer text-end">
                    <h4>Total: <strong>{{ number_format($monto_total, 2) }} Bs</strong></h4>

                    <a href="{{ route('salidas.index') }}" class="btn btn-secondary">
                        Volver
                    </a>
                </div>

            </div>
        </div>
    </div>

</main>

{{-- MODAL PDF --}}
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" style="max-width:90%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vista previa - Comprobante de Salida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="height: 80vh; padding:0;">
        <iframe id="pdfFrame" src="" frameborder="0" style="width:100%; height:100%;"></iframe>
      </div>

      <div class="modal-footer">
        <a id="pdfDownload"
           href="{{ route('salidas.pdf', $salida->id) }}"
           class="btn btn-secondary"
           target="_blank">
            Abrir en pestaña
        </a>

        <button type="button" class="btn btn-primary"
            onclick="document.getElementById('pdfFrame').contentWindow.print();">
            Imprimir
        </button>

        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('pdfModal')

    modal.addEventListener('show.bs.modal', function () {
        document.getElementById('pdfFrame').src =
            "{{ route('salidas.pdf', $salida->id) }}";
    })

    modal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('pdfFrame').src = '';
    })
});
</script>
@endpush

@endsection
