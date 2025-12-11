@extends('dashboard-layouts.header-footer')

@section('content')
<div class="app-main">
    <div class="container-fluid">
        <h3 class="mb-4">Productos con Stock Mínimo</h3>

        @if($productos->isEmpty())
            <div class="alert alert-info">No hay productos con stock mínimo.</div>
        @else
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Stock Mínimo</th>
                    <th>En Pedido</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($productos as $producto)
                <tr>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->categoria->nombre ?? 'Sin categoría' }}</td>
                    <td>{{ $producto->stock }}</td>
                    <td>{{ $producto->stock_minimo }}</td>
                    <td>{{ $producto->en_pedido }}</td>
                    <td>
                        <a href="{{ route('productos.show', $producto) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('productos.edit', $producto) }}" class="btn btn-warning btn-sm">Editar</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $productos->links() }}
        @endif
    </div>
</div>
@endsection
