<?php

use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\TipoIngresoController;
use App\Http\Controllers\TipoSalidaController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';


Route::middleware('auth')->group(function () {
    Route::get('/almacenes', [AlmacenController::class, 'index'])->name('almacenes.index');
    Route::get('/almacenes/create', [AlmacenController::class, 'create'])->name('almacenes.create');
    Route::post('/almacenes', [AlmacenController::class, 'store'])->name('almacenes.store');
    Route::get('/almacenes/{almacen}', [AlmacenController::class, 'show'])->name('almacenes.show');
    Route::get('/almacenes/{almacen}/edit', [AlmacenController::class, 'edit'])->name('almacenes.edit');
    Route::put('/almacenes/{almacen}', [AlmacenController::class, 'update'])->name('almacenes.update');
    Route::delete('/almacenes/{almacen}', [AlmacenController::class, 'destroy'])->name('almacenes.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('tiposalidas', [TipoSalidaController::class, 'index'])->name('tiposalidas.index');
    Route::get('tiposalidas/create', [TipoSalidaController::class, 'create'])->name('tiposalidas.create');
    Route::post('tiposalidas', [TipoSalidaController::class, 'store'])->name('tiposalidas.store');
    Route::get('tiposalidas/{tipoSalida}', [TipoSalidaController::class, 'show'])->name('tiposalidas.show');
    Route::get('tiposalidas/{tipoSalida}/edit', [TipoSalidaController::class, 'edit'])->name('tiposalidas.edit');
    Route::put('tiposalidas/{tipoSalida}', [TipoSalidaController::class, 'update'])->name('tiposalidas.update');
    Route::delete('tiposalidas/{tipoSalida}', [TipoSalidaController::class, 'destroy'])->name('tiposalidas.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('tipoingresos', [TipoIngresoController::class, 'index'])->name('tipoingresos.index');
    Route::get('tipoingresos/create', [TipoIngresoController::class, 'create'])->name('tipoingresos.create');
    Route::post('tipoingresos', [TipoIngresoController::class, 'store'])->name('tipoingresos.store');
    Route::get('tipoingresos/{tipoIngreso}', [TipoIngresoController::class, 'show'])->name('tipoingresos.show');
    Route::get('tipoingresos/{tipoIngreso}/edit', [TipoIngresoController::class, 'edit'])->name('tipoingresos.edit');
    Route::put('tipoingresos/{tipoIngreso}', [TipoIngresoController::class, 'update'])->name('tipoingresos.update');
    Route::delete('tipoingresos/{tipoIngreso}', [TipoIngresoController::class, 'destroy'])->name('tipoingresos.destroy');
});
Route::middleware('auth')->group(function () {

    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
    Route::get('/categorias/create', [CategoriaController::class, 'create'])->name('categorias.create');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::get('/categorias/{categoria}', [CategoriaController::class, 'show'])->name('categorias.show');
    Route::get('/categorias/{categoria}/edit', [CategoriaController::class, 'edit'])->name('categorias.edit');
    Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
});
Route::middleware('auth')->group(function () {
    Route::get('/vehiculos', [VehiculoController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/create', [VehiculoController::class, 'create'])->name('vehiculos.create');
    Route::post('/vehiculos', [VehiculoController::class, 'store'])->name('vehiculos.store');
    Route::get('/vehiculos/{vehiculo}', [VehiculoController::class, 'show'])->name('vehiculos.show');
    Route::get('/vehiculos/{vehiculo}/edit', [VehiculoController::class, 'edit'])->name('vehiculos.edit');
    Route::put('/vehiculos/{vehiculo}', [VehiculoController::class, 'update'])->name('vehiculos.update');
    Route::delete('/vehiculos/{vehiculo}', [VehiculoController::class, 'destroy'])->name('vehiculos.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
    Route::get('/productos/create', [ProductoController::class, 'create'])->name('productos.create');
    Route::post('/productos', [ProductoController::class, 'store'])->name('productos.store');
    Route::get('/productos/{producto}', [ProductoController::class, 'show'])->name('productos.show');
    Route::get('/productos/{producto}/edit', [ProductoController::class, 'edit'])->name('productos.edit');
    Route::put('/productos/{producto}', [ProductoController::class, 'update'])->name('productos.update');
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])->name('productos.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('/unidad-medidas', [UnidadMedidaController::class, 'index'])->name('unidad-medidas.index');
    Route::get('/unidad-medidas/create', [UnidadMedidaController::class, 'create'])->name('unidad-medidas.create');
    Route::post('/unidad-medidas', [UnidadMedidaController::class, 'store'])->name('unidad-medidas.store');
    Route::get('/unidad-medidas/{unidadMedida}', [UnidadMedidaController::class, 'show'])->name('unidad-medidas.show');
    Route::get('/unidad-medidas/{unidadMedida}/edit', [UnidadMedidaController::class, 'edit'])->name('unidad-medidas.edit');
    Route::put('/unidad-medidas/{unidadMedida}', [UnidadMedidaController::class, 'update'])->name('unidad-medidas.update');
    Route::delete('/unidad-medidas/{unidadMedida}', [UnidadMedidaController::class, 'destroy'])->name('unidad-medidas.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
});

Route::middleware('auth')->group(function () {

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/create', [PedidoController::class, 'create'])->name('pedidos.create');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::get('/pedidos/{pedido}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit');
    Route::put('/pedidos/{pedido}', [PedidoController::class, 'update'])->name('pedidos.update');
    Route::delete('/pedidos/{pedido}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');
    // Cambiar el estado del pedido
    Route::put('/pedidos/{pedido}/confirmar', [PedidoController::class, 'confirmar'])
        ->name('pedidos.confirmar');
    Route::put('/pedidos/{pedido}/anular', [PedidoController::class, 'anular'])
        ->name('pedidos.anular');

    // REPORTE EN PDF
    Route::get('/pedidos/{pedido}/pdf', [PedidoController::class, 'generarPDF'])
        ->name('pedidos.pdf');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/ingresos', [IngresoController::class, 'index'])->name('ingresos.index');
    Route::get('/ingresos/create', [IngresoController::class, 'create'])->name('ingresos.create');
    Route::post('/ingresos', [IngresoController::class, 'store'])->name('ingresos.store');

    Route::get('/ingresos/{ingreso}', [IngresoController::class, 'show'])->name('ingresos.show');
    Route::get('/ingresos/{ingreso}/edit', [IngresoController::class, 'edit'])->name('ingresos.edit');
    Route::put('/ingresos/{ingreso}', [IngresoController::class, 'update'])->name('ingresos.update');

    Route::delete('/ingresos/{ingreso}', [IngresoController::class, 'destroy'])->name('ingresos.destroy');

    Route::put('/ingresos/{ingreso}/cambiar-estado', [IngresoController::class, 'cambiarEstado'])
        ->name('ingresos.cambiarEstado');

    // imprimir comprobante en PDF
    Route::get('/ingresos/{ingreso}/pdf', [IngresoController::class, 'pdf'])
        ->name('ingresos.pdf');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/salidas', [SalidaController::class, 'index'])->name('salidas.index');
    Route::get('/salidas/create', [SalidaController::class, 'create'])->name('salidas.create');
    Route::post('/salidas', [SalidaController::class, 'store'])->name('salidas.store');

    Route::get('/salidas/{salida}', [SalidaController::class, 'show'])->name('salidas.show');
    Route::get('/salidas/{salida}/edit', [SalidaController::class, 'edit'])->name('salidas.edit');
    Route::put('/salidas/{salida}', [SalidaController::class, 'update'])->name('salidas.update');

    Route::delete('/salidas/{salida}', [SalidaController::class, 'destroy'])->name('salidas.destroy');

    Route::put('/salidas/{salida}/estado', [SalidaController::class, 'cambiarEstado'])
        ->name('salidas.cambiarEstado');

    Route::get('/salidas/{salida}/pdf', [SalidaController::class, 'pdf'])
        ->name('salidas.pdf');
});

Route::middleware(['auth'])->group(function () {
    Route::prefix('reportes')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/salidas', [ReporteController::class, 'salidas'])->name('reportes.salidas');
        Route::get('/ingresos', [ReporteController::class, 'ingresos'])->name('reportes.ingresos');

        // PDF
        Route::get('/salidas/pdf', [ReporteController::class, 'salidasPdf'])->name('reportes.salidas.pdf');
        Route::get('/ingresos/pdf', [ReporteController::class, 'ingresosPdf'])->name('reportes.ingresos.pdf');
    });
});
