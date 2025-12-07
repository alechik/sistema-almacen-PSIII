@extends('dashboard-layouts.header-footer')

@section('content')
<main class="app-main">
    <div class="container-fluid mt-3">
        <h3>Reportes</h3>
        <div class="list-group">
            <a href="{{ route('reportes.salidas') }}" class="list-group-item list-group-item-action">
                Reporte de Salidas
            </a>
            <a href="{{ route('reportes.ingresos') }}" class="list-group-item list-group-item-action">
                Reporte de Ingresos
            </a>
        </div>
    </div>
</main>
@endsection
