<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidad_medidas';

    protected $fillable = [
        'cod_unidad_medida',
        'descripcion',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'unidad_medida_id');
    }
}
