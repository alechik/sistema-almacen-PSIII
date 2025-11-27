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
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text"
                                       name="nombre"
                                       id="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $producto->nombre) }}">
                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Código -->
                            <div class="col-md-6 mb-3">
                                <label for="cod_producto" class="form-label">Código *</label>
                                <input type="text"
                                       name="cod_producto"
                                       id="cod_producto"
                                       class="form-control @error('cod_producto') is-invalid @enderror"
                                       value="{{ old('cod_producto', $producto->cod_producto) }}">
                                @error('cod_producto')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-6 mb-3">
                                <label for="categoria_id" class="form-label">Categoría *</label>
                                <select name="categoria_id"
                                        id="categoria_id"
                                        class="form-select @error('categoria_id') is-invalid @enderror">
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

                            <!-- Proveedor (array) -->
                            <div class="col-md-6 mb-3">
                                <label for="proveedor_id" class="form-label">Proveedor *</label>
                                <select name="proveedor_id"
                                        id="proveedor_id"
                                        class="form-select @error('proveedor_id') is-invalid @enderror">
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

                            <!-- Stock y Stock mínimo -->
                            <div class="col-md-4 mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number"
                                       name="stock"
                                       class="form-control"
                                       value="{{ old('stock', $producto->stock) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="stock_minimo" class="form-label">Stock mínimo</label>
                                <input type="number"
                                       name="stock_minimo"
                                       class="form-control"
                                       value="{{ old('stock_minimo', $producto->stock_minimo) }}">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="precio" class="form-label">Precio</label>
                                <input type="number"
                                       step="0.01"
                                       name="precio"
                                       class="form-control"
                                       value="{{ old('precio', $producto->precio) }}">
                            </div>

                            <!-- Fecha vencimiento -->
                            <div class="col-md-6 mb-3">
                                <label for="fech_vencimiento" class="form-label">Fecha de vencimiento</label>
                                <input type="date"
                                       name="fech_vencimiento"
                                       class="form-control"
                                       value="{{ old('fech_vencimiento', $producto->fech_vencimiento) }}">
                            </div>

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
