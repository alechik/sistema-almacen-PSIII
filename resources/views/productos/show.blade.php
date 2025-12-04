@extends('dashboard-layouts.header-footer')
@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Detalle del Producto</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
                        <li class="breadcrumb-item active">Ver Detalle</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Información del Producto</h3>
                    <a href="{{ route('productos.index') }}" class="btn btn-secondary btn-sm ms-auto">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>

                <div class="card-body">
                    <div class="row">

                        <!-- Código -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Código:</label>
                            <p>{{ $producto->cod_producto }}</p>
                        </div>

                        <!-- Nombre -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Nombre:</label>
                            <p>{{ $producto->nombre }}</p>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Estado:</label>
                            <p>
                                @if ($producto->estado == 1 || $producto->estado == 'activo')
                                    <span class="badge bg-success px-3 py-1">Activo</span>
                                @else
                                    <span class="badge bg-danger px-3 py-1">Inactivo</span>
                                @endif
                            </p>
                        </div>

                        <!-- Descripción -->
                        <div class="col-md-12 mb-3">
                            <label class="fw-bold">Descripción:</label>
                            <p>{{ $producto->descripcion ?? 'Sin descripción' }}</p>
                        </div>

                        <!-- Categoría -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Categoría:</label>
                            <p>{{ $producto->categoria->nombre ?? '-' }}</p>
                        </div>

                        <!-- Proveedor -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Proveedor:</label>
                            <p>{{ $proveedor['nombre'] ?? '-' }}</p>
                        </div>

                        <!-- Unidad de Medida -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Unidad de Medida:</label>
                            <p>{{ $producto->unidadMedida->cod_unidad_medida ?? '-' }}</p>
                        </div>

                        <!-- Precio -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Precio:</label>
                            <p>{{ number_format($producto->precio, 2) }} Bs</p>
                        </div>

                        <!-- Stock -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Stock:</label>
                            <p>{{ $producto->stock ?? 0 }}</p>
                        </div>

                        <!-- Stock mínimo -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Stock Mínimo:</label>
                            <p>{{ $producto->stock_minimo ?? 0 }}</p>
                        </div>

                        <!-- Fecha de vencimiento -->
                        <div class="col-md-4 mb-3">
                            <label class="fw-bold">Fecha de vencimiento:</label>
                            <p>{{ $producto->fech_vencimiento ? \Carbon\Carbon::parse($producto->fech_vencimiento)->format('d/m/Y') : '-' }}</p>
                        </div>

                        <!-- Fechas del sistema -->
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Fecha creación:</label>
                            <p>{{ $producto->created_at->format('d/m/Y H:i') }}</p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Última actualización:</label>
                            <p>{{ $producto->updated_at->format('d/m/Y H:i') }}</p>
                        </div>

                    </div>
                </div>

                <!-- Footer botones -->
                <div class="card-footer d-flex justify-content-end gap-2">

                    <a href="{{ route('productos.edit', $producto) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>

                    <form action="{{ route('productos.destroy', $producto) }}" method="POST"
                          onsubmit="return confirm('¿Cambiar el estado del producto?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                            {{ $producto->estado == 1 ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                </div>

            </div>

        </div>
    </div>

</main>

@endsection
