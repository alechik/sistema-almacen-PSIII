<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;

class ProductoStockMinimoController extends Controller
{
    public function index()
    {
        $productos = Producto::with('categoria')
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->where('en_pedido', 0)
            ->where('estado', 1)
            ->orderBy('stock')
            ->paginate(10);

        return view('productos.stock-minimo', compact('productos'));
    }
}
