@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Editar Producto</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('productos.index') }}">Productos</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

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

                <div class="card-header">
                    <h3 class="card-title">Actualizar Producto</h3>
                </div>

                <div class="card-body">

                    <form action="{{ route('productos.update', $producto->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">

                            <!-- Nombre -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $producto->nombre) }}">
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Código -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Código *</label>
                                <input type="text" name="cod_producto"
                                       class="form-control @error('cod_producto') is-invalid @enderror"
                                       value="{{ old('cod_producto', $producto->cod_producto) }}" readonly>
                                @error('cod_producto')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría *</label>
                                <select name="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach ($categorias as $cat)
                                        <option value="{{ $cat->id }}"
                                            {{ $cat->id == old('categoria_id', $producto->categoria_id) ? 'selected' : '' }}>
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
                                <select name="proveedor_id" class="form-select @error('proveedor_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach ($proveedores as $prov)
                                        <option value="{{ $prov['id'] }}"
                                            {{ $prov['id'] == old('proveedor_id', $producto->proveedor_id) ? 'selected' : '' }}>
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
                                <label class="form-label">Unidad de medida *</label>
                                <select name="unidad_medida_id"
                                        class="form-select @error('unidad_medida_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>
                                    @foreach ($unidades as $uni)
                                        <option value="{{ $uni->id }}"
                                            {{ $uni->id == old('unidad_medida_id', $producto->unidad_medida_id) ? 'selected' : '' }}>
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
                                       class="form-control"
                                       value="{{ old('stock', $producto->stock) }}" readonly>
                            </div>

                            <!-- Stock mínimo -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock mínimo</label>
                                <input type="number" step="0.01" name="stock_minimo"
                                       class="form-control"
                                       value="{{ old('stock_minimo', $producto->stock_minimo) }}" readonly>
                            </div>

                            <!-- Precio -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Precio</label>
                                <input type="number" step="0.01" name="precio"
                                       class="form-control"
                                       value="{{ old('precio', $producto->precio) }}" readonly>
                            </div>

                            <!-- Fecha Vencimiento -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de vencimiento</label>
                                <input type="date" name="fech_vencimiento"
                                       class="form-control"
                                       value="{{ old('fech_vencimiento', $producto->fech_vencimiento) }}">
                            </div>

                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" rows="3" class="form-control">{{ old('descripcion', $producto->descripcion) }}</textarea>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Actualizar
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>
    </div>

</main>

@endsection
