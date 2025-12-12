@extends('dashboard-layouts.header-footer')

@section('content')

<main class="app-main">

    <!-- Header -->
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Editar Asignación de Producto</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('almacen-productos.index') }}">Asignaciones</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="app-content">
        <div class="container-fluid">

            {{-- ERRORES --}}
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Hay errores en el formulario:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-warning alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">

                <div class="card-header">
                    <h3 class="card-title">Actualizar Asignación</h3>
                </div>

                <div class="card-body">

                    <form action="{{ route('almacen-productos.update', $almacenProducto->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">

                            {{-- Almacén --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Almacén *</label>
                                <select name="almacen_id"
                                        class="form-select @error('almacen_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>

                                    @foreach ($almacenes as $alm)
                                        <option value="{{ $alm->id }}"
                                            {{ old('almacen_id', $almacenProducto->almacen_id) == $alm->id ? 'selected' : '' }}>
                                            {{ $alm->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('almacen_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Producto --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Producto *</label>
                                <select name="producto_id"
                                        class="form-select @error('producto_id') is-invalid @enderror">
                                    <option value="">Seleccione...</option>

                                    @foreach ($productos as $p)
                                        <option value="{{ $p->id }}"
                                            {{ old('producto_id', $almacenProducto->producto_id) == $p->id ? 'selected' : '' }}>
                                            {{ $p->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('producto_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Stock --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock *</label>
                                <input type="number" step="0.01" name="stock"
                                       class="form-control @error('stock') is-invalid @enderror"
                                       value="{{ old('stock', $almacenProducto->stock) }}">
                                @error('stock')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- Stock mínimo --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock mínimo *</label>
                                <input type="number" step="0.01" name="stock_minimo"
                                       class="form-control @error('stock_minimo') is-invalid @enderror"
                                       value="{{ old('stock_minimo', $almacenProducto->stock_minimo) }}">
                                @error('stock_minimo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- En pedido --}}
                            <div class="col-md-4 mb-3">
                                <label class="form-label">En pedido *</label>
                                <select name="en_pedido"
                                        class="form-select @error('en_pedido') is-invalid @enderror">
                                    <option value="0" {{ old('en_pedido', $almacenProducto->en_pedido) == 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('en_pedido', $almacenProducto->en_pedido) == 1 ? 'selected' : '' }}>Sí</option>
                                </select>
                                @error('en_pedido')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <!-- BOTONES -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('almacen-productos.index') }}" class="btn btn-secondary">
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
