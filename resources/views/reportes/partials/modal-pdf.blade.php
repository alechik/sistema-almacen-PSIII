{{-- partials/modal-pdf.blade.php --}}
<div class="modal fade" id="{{ $modalId ?? 'pdfModal' }}" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" style="max-width: 90%;">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          {{ $title ?? 'Vista previa PDF' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="height: 80vh; padding: 0;">
        <iframe
            id="{{ $iframeId ?? 'pdfFrame' }}"
            src=""
            frameborder="0"
            style="width: 100%; height: 100%;">
        </iframe>
      </div>

      <div class="modal-footer">

        <a
           id="{{ $downloadId ?? 'pdfDownload' }}"
           href="{{ $routePdf }}"
           class="btn btn-secondary"
           target="_blank">
            Abrir en pestaña
        </a>

        <button type="button" class="btn btn-primary"
            onclick="document.getElementById('{{ $iframeId ?? 'pdfFrame' }}').contentWindow.print();">
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
document.addEventListener('DOMContentLoaded', function () {
    let modal = document.getElementById('{{ $modalId ?? 'pdfModal' }}');

    modal.addEventListener('show.bs.modal', function () {
        document.getElementById('{{ $iframeId ?? 'pdfFrame' }}').src =
            "{{ $routePdf }}";
    });

    modal.addEventListener('hidden.bs.modal', function () {
        document.getElementById('{{ $iframeId ?? 'pdfFrame' }}').src = '';
    });
});
</script>
@endpush
