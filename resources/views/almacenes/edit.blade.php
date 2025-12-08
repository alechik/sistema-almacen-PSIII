@extends('dashboard-layouts.header-footer')
@section('content')
@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush
<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Editar Almacén</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}">Almacenes</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <!-- Errores -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Hay errores en el formulario:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Datos del Almacén</h3>
                    <a href="{{ route('almacenes.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <form action="{{ route('almacenes.update', $almacen) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        <div class="row">

                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre" 
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $almacen->nombre) }}" required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado *</label>
                                <select name="estado" 
                                        class="form-select @error('estado') is-invalid @enderror" 
                                        required>

                                    <option value="">-- Seleccione un estado --</option>

                                    <option value="ACTIVADO" 
                                        {{ old('estado', $almacen->estado) == 'ACTIVADO' ? 'selected' : '' }}>
                                        ACTIVADO
                                    </option>

                                    <option value="DESACTIVADO" 
                                        {{ old('estado', $almacen->estado) == 'DESACTIVADO' ? 'selected' : '' }}>
                                        DESACTIVADO
                                    </option>

                                    <option value="CERRADO" 
                                        {{ old('estado', $almacen->estado) == 'CERRADO' ? 'selected' : '' }}>
                                        CERRADO
                                    </option>
                                </select>

                                @error('estado')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Ubicación -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Ubicación</label>
                                <textarea name="ubicacion" rows="2"
                                          class="form-control @error('ubicacion') is-invalid @enderror">{{ old('ubicacion', $almacen->ubicacion) }}</textarea>
                                @error('ubicacion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Longitud -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Longitud</label>
                                <input type="text" name="longitud" 
                                       class="form-control @error('longitud') is-invalid @enderror"
                                       value="{{ old('longitud', $almacen->longitud) }}">
                                @error('longitud')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Latitud -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Latitud</label>
                                <input type="text" name="latitud" 
                                       class="form-control @error('latitud') is-invalid @enderror"
                                       value="{{ old('latitud', $almacen->latitud) }}">
                                @error('latitud')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-6">
                                {{-- <label class="form-label">Ubicación en el Mapa</label> --}}
                                <!-- Botón Detectar ubicación -->
                                <button type="button" id="btnDetectar" class="btn btn-success btn-sm mb-2">
                                    <i class="fas fa-location-arrow"></i> Detectar mi ubicación
                                </button>

                                <div id="map" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                            </div>

                            <!-- Celular -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Celular</label>
                                <input type="text" name="cellphone"
                                       class="form-control @error('cellphone') is-invalid @enderror"
                                       value="{{ old('cellphone', $almacen->cellphone) }}">
                                @error('cellphone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $almacen->email) }}">
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" rows="3"
                                          class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $almacen->descripcion) }}</textarea>
                                @error('descripcion')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar Almacén
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>

</main>

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- jQuery (debe ir antes que Toastr) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    // Coordenadas del almacén desde Laravel
    var almacenLat = "{{ $almacen->latitud }}";
    var almacenLng = "{{ $almacen->longitud }}";

    // Coordenadas por defecto si no hay datos
    var defaultLat = -17.4000;
    var defaultLng = -66.1653;

    // Determinar el punto inicial del mapa
    var initialLat = almacenLat ? parseFloat(almacenLat) : defaultLat;
    var initialLng = almacenLng ? parseFloat(almacenLng) : defaultLng;

    // Crear mapa
    var map = L.map('map').setView([initialLat, initialLng], 14);

    // Mapa base
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    // Crear marcador
    var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

    // Si el almacén NO tiene coordenadas → usar ubicación real
    if (!almacenLat || !almacenLng) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    var lat = pos.coords.latitude.toFixed(6);
                    var lng = pos.coords.longitude.toFixed(6);

                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);

                    document.querySelector("input[name='latitud']").value = lat;
                    document.querySelector("input[name='longitud']").value = lng;
                },
                function (err) {
                    console.warn("No se pudo obtener la ubicación:", err.message);
                }
            );
        }
    }

    // 1️⃣ Al mover el marcador
    marker.on('dragend', function () {
        var lat = marker.getLatLng().lat.toFixed(6);
        var lng = marker.getLatLng().lng.toFixed(6);
        document.querySelector("input[name='latitud']").value = lat;
        document.querySelector("input[name='longitud']").value = lng;
    });

    // 2️⃣ Al hacer click en el mapa
    map.on('click', function (e) {
        var lat = e.latlng.lat.toFixed(6);
        var lng = e.latlng.lng.toFixed(6);
        marker.setLatLng([lat, lng]);

        document.querySelector("input[name='latitud']").value = lat;
        document.querySelector("input[name='longitud']").value = lng;
    });

// BOTÓN: DETECTAR MI UBICACIÓN
document.getElementById("btnDetectar").addEventListener("click", function () {

    if (!navigator.geolocation) {
        alert("La geolocalización no está habilitada en este navegador.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            var lat = pos.coords.latitude.toFixed(6);
            var lng = pos.coords.longitude.toFixed(6);

            // Mover el mapa y el marcador
            map.setView([lat, lng], 16);
            marker.setLatLng([lat, lng]);

            // Rellenar inputs
            document.querySelector("input[name='latitud']").value = lat;
            document.querySelector("input[name='longitud']").value = lng;

            // Mensaje opcional
            toastr.success("Ubicación detectada correctamente.");
        },
        function (err) {
            alert("Error al obtener tu ubicación: " + err.message);
        }
    );
});


});
</script>



@endpush

@endsection
