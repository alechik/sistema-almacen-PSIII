@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <div class="app-content-header">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Detalle del Ingreso</h3>

            <ol class="breadcrumb float-sm-end">
                <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ingresos.index') }}">Ingresos</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <!-- DATOS GENERALES -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Datos Generales del Ingreso</h5>
                    <button type="button" class="btn btn-danger btn-sm ms-auto"
                        data-bs-toggle="modal" data-bs-target="#pdfModal">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i>
                        PDF
                    </button>
                </div>

                <div class="card-body row">

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Código Comprobante:</label>
                        <p>{{ $ingreso->codigo_comprobante }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Fecha:</label>
                        <p>{{ $ingreso->fecha }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Proveedor:</label>
                        <p>{{ $proveedor_nombre }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Almacén:</label>
                        <p>{{ $ingreso->almacen->nombre }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Operador:</label>
                        <p>{{ $ingreso->operador->full_name }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Transportista:</label>
                        <p>{{ $ingreso->transportista->full_name }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Vehículo:</label>
                        <p>{{ $ingreso->vehiculo?->placa_identificacion ?? 'No asignado' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Tipo de Ingreso:</label>
                        <p>{{ $ingreso->tipoIngreso->nombre }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold">Pedido Asociado:</label>
                        <p>{{ $ingreso->pedido->codigo_comprobante }}</p>
                    </div>

                </div>
            </div>

            <!-- DETALLE -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Detalle del Ingreso</h5>
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
                            @foreach($ingreso->detalles as $d)
                                <tr>
                                    <td>{{ $d->producto->nombre }}</td>
                                    <td>{{ $d->cant_ingreso }}</td>
                                    <td>{{ number_format($d->precio, 4) }}</td>
                                    <td>{{ number_format($d->cant_ingreso * $d->precio, 2) }} Bs</td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

                <div class="card-footer text-end">
                    <h4>Total: <strong>{{ number_format($monto_total, 2) }} Bs</strong></h4>

                    <a href="{{ route('ingresos.index') }}" class="btn btn-secondary">
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
        <h5 class="modal-title">Vista previa - Comprobante de Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="height: 80vh; padding:0;">
        <iframe id="pdfFrame" src="" frameborder="0" style="width:100%; height:100%;"></iframe>
      </div>

      <div class="modal-footer">
        <a id="pdfDownload" href="{{ route('ingresos.pdf', $ingreso->id) }}" class="btn btn-secondary" target="_blank">
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
            "{{ route('ingresos.pdf', $ingreso->id) }}";
    })

    modal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('pdfFrame').src = '';
    })
});
</script>
@endpush

@endsection
