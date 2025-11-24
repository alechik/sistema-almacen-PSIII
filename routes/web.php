<?php

use App\Http\Controllers\AlmacenController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TipoIngresoController;
use App\Http\Controllers\TipoSalidaController;
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