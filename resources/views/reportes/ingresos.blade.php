@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="container-fluid mt-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Reporte de Ingresos</h3>

            <button type="button" class="btn btn-danger"
                data-bs-toggle="modal" data-bs-target="#pdfModalIngresos">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
            </button>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cód. Comprobante</th>
                    <th>Almacén</th>
                    <th>Administrador</th>
                    <th>Fecha</th>
                    <th>Cant. Total</th>
                    <th>Monto Total</th>
                </tr>
            </thead>

            <tbody>
                @foreach($ingresos as $i)
                <tr>
                    <td>{{ $i->id }}</td>
                    <td>{{ $i->codigo_comprobante }}</td>
                    <td>{{ $i->almacen->nombre ?? '—' }}</td>
                    <td>{{ $i->administrador->full_name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($i->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $i->detalles->sum('cant_ingreso') }}</td>
                    <td>
                        {{ number_format($i->detalles->sum(fn($d)=>$d->cant_ingreso * $d->precio), 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</main>

{{-- MODAL PDF --}}
<div class="modal fade" id="pdfModalIngresos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" style="max-width: 90%;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Vista previa - Reporte General de Ingresos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="height: 80vh; padding: 0;">
        <iframe id="pdfFrameIngresos" src="" frameborder="0"
            style="width: 100%; height: 100%;"></iframe>
      </div>

      <div class="modal-footer">

        <a id="pdfDownloadIngresos"
           href="{{ route('reportes.ingresos.pdf') }}"
           class="btn btn-secondary"
           target="_blank">
            Abrir en pestaña
        </a>

        <button type="button" class="btn btn-primary"
            onclick="document.getElementById('pdfFrameIngresos').contentWindow.print();">
            Imprimir
        </button>

        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
            Cerrar
        </button>
      </div>

    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('pdfModalIngresos')

    modal.addEventListener('show.bs.modal', function () {
        document.getElementById('pdfFrameIngresos').src =
            "{{ route('reportes.ingresos.pdf') }}";
    })

    modal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('pdfFrameIngresos').src = '';
    })
});
</script>
@endpush
