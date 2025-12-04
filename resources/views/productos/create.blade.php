@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Nuevo Producto</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
                        <li class="breadcrumb-item active">Nuevo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Hay errores:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">Registrar Producto</h3>
                </div>

                <div class="card-body">

                    <form action="{{ route('productos.store') }}" method="POST">
                        @csrf

                        <div class="row">

                            <!-- Código -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código *</label>
                                <input type="text" name="cod_producto"
                                       class="form-control @error('cod_producto') is-invalid @enderror"
                                       value="{{ old('cod_producto') }}">
                                @error('cod_producto')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre') }}">
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría *</label>
                                <select name="categoria_id"
                                        class="form-select @error('categoria_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach ($categorias as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('categoria_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Proveedor -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Proveedor *</label>
                                <select name="proveedor_id"
                                        class="form-select @error('proveedor_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach ($proveedores as $prov)
                                        <option value="{{ $prov['id'] }}"
                                            {{ old('proveedor_id') == $prov['id'] ? 'selected' : '' }}>
                                            {{ $prov['nombre'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('proveedor_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Unidad de Medida -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Unidad de Medida *</label>
                                <select name="unidad_medida_id"
                                        class="form-select @error('unidad_medida_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach ($unidades as $uni)
                                        <option value="{{ $uni->id }}"
                                            {{ old('unidad_medida_id') == $uni->id ? 'selected' : '' }}>
                                            {{ $uni->cod_unidad_medida }} - {{ $uni->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unidad_medida_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Stock -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock</label>
                                <input type="number" step="0.01" name="stock"
                                       class="form-control @error('stock') is-invalid @enderror"
                                       value="{{ old('stock') }}">
                            </div>

                            <!-- Stock mínimo -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock mínimo</label>
                                <input type="number" step="0.01" name="stock_minimo"
                                       class="form-control @error('stock_minimo') is-invalid @enderror"
                                       value="{{ old('stock_minimo') }}">
                            </div>

                            <!-- Precio -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Precio</label>
                                <input type="number" step="0.01" name="precio"
                                       class="form-control @error('precio') is-invalid @enderror"
                                       value="{{ old('precio') }}">
                            </div>

                            <!-- Fecha Vencimiento -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de vencimiento</label>
                                <input type="date" name="fech_vencimiento"
                                       class="form-control @error('fech_vencimiento') is-invalid @enderror"
                                       value="{{ old('fech_vencimiento') }}">
                            </div>

                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" rows="3" class="form-control">{{ old('descripcion') }}</textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

</main>

@endsection
