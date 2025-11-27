<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'stock',
        'fech_vencimiento',
        'estado',
        'stock_minimo',
        'categoria_id',
        'cod_producto',
        'precio',
        'proveedor_id'
    ];

    /*
    * RELACIONES CON LOS OTROS MODELOS
    */

    // Producto pertenece a una Categoría
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // Producto pertenece a un Proveedor
    // public function proveedor()
    // {
    //     return $this->belongsTo(Proveedor::class);
    // }

    // Relación con detalle de ingresos
    // public function detallesIngreso()
    // {
    //     return $this->hasMany(DetalleIngreso::class);
    // }

    // Relación con detalle de salidas
    // public function detallesSalida()
    // {
    //     return $this->hasMany(DetalleSalida::class);
    // }

    // Relación con detalle de pedidos
    // public function detallesPedido()
    // {
    //     return $this->hasMany(DetallePedido::class);
    // }
}
