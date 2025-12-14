@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="container-fluid mt-3">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Salidas por producto y almacén</h3>

            <button class="btn btn-danger"
                data-bs-toggle="modal"
                data-bs-target="#modalSalidasProducto">
                <i class="bi bi-file-earmark-pdf-fill"></i> PDF
            </button>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Almacén</th>
                    <th>Producto</th>
                    <th class="text-end">Cantidad total</th>
                    <th class="text-end">Costo total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $row)
                <tr>
                    <td>{{ $row->almacen }}</td>
                    <td>{{ $row->producto }}</td>
                    <td class="text-end">{{ number_format($row->cantidad_total, 2) }}</td>
                    <td class="text-end">{{ number_format($row->costo_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</main>

@include('reportes.partials.modal-pdf', [
    'modalId'  => 'modalSalidasProducto',
    'iframeId' => 'frameSalidasProducto',
    'title'    => 'Reporte de Salidas por Producto y Almacén',
    'routePdf' => route('reportes.salidas.productos.almacen.pdf')
])
@endsection
