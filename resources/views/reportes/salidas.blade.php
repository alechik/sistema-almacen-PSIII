@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="container-fluid mt-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Reporte de Salidas</h3>

            <!-- BOTÓN PDF ESTILO SHOW -->
            <button type="button" class="btn btn-danger"
                data-bs-toggle="modal" data-bs-target="#pdfModalReporte">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF
            </button>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cod. Comp.</th>
                    <th>Almacén</th>
                    <th>Operador</th>
                    <th>Transportista</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>
                @foreach($salidas as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->codigo_comprobante }}</td>
                    <td>{{ $s->almacen->nombre ?? '—' }}</td>
                    <td>{{ $s->operador->full_name ?? '—' }}</td>
                    <td>{{ $s->transportista->full_name ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($s->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $s->estado }}</td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</main>


{{-- =================== MODAL PDF REPORTE GENERAL =================== --}}
<div class="modal fade" id="pdfModalReporte" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" style="max-width: 90%;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Vista previa - Reporte General de Salidas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="height: 80vh; padding: 0;">
        <iframe id="pdfFrameReporte" src="" frameborder="0"
            style="width: 100%; height: 100%;"></iframe>
      </div>

      <div class="modal-footer">

        <!-- ABRIR EN PESTAÑA NUEVA -->
        <a id="pdfDownloadReporte"
           href="{{ route('reportes.salidas.pdf') }}"
           class="btn btn-secondary"
           target="_blank">
            Abrir en pestaña
        </a>

        <!-- IMPRIMIR DIRECTO -->
        <button type="button" class="btn btn-primary"
            onclick="document.getElementById('pdfFrameReporte').contentWindow.print();">
            Imprimir
        </button>

        <!-- CERRAR -->
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
    var modal = document.getElementById('pdfModalReporte')

    modal.addEventListener('show.bs.modal', function () {
        document.getElementById('pdfFrameReporte').src =
            "{{ route('reportes.salidas.pdf') }}";
    })

    modal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('pdfFrameReporte').src = '';
    })
});
</script>
@endpush
