<?php

namespace Database\Seeders;

use App\Models\UnidadMedida;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnidadMedidaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $unidades = [
            ['cod_unidad_medida' => 'UND', 'descripcion' => 'Unidad'],
            ['cod_unidad_medida' => 'KG',  'descripcion' => 'Kilogramo'],
            ['cod_unidad_medida' => 'G',   'descripcion' => 'Gramo'],
            ['cod_unidad_medida' => 'LT',  'descripcion' => 'Litro'],
            ['cod_unidad_medida' => 'ML',  'descripcion' => 'Mililitro'],
            ['cod_unidad_medida' => 'M',   'descripcion' => 'Metro'],
            ['cod_unidad_medida' => 'CM',  'descripcion' => 'Centímetro'],
            ['cod_unidad_medida' => 'MT2', 'descripcion' => 'Metro cuadrado'],
            ['cod_unidad_medida' => 'MT3', 'descripcion' => 'Metro cúbico'],
            ['cod_unidad_medida' => 'PZA', 'descripcion' => 'Pieza'],
            ['cod_unidad_medida' => 'CJ',  'descripcion' => 'Caja'],
            ['cod_unidad_medida' => 'PQ',  'descripcion' => 'Paquete'],
            ['cod_unidad_medida' => 'GLB', 'descripcion' => 'Galón'],
        ];

        foreach ($unidades as $uni) {
            UnidadMedida::firstOrCreate(['cod_unidad_medida' => $uni['cod_unidad_medida']], $uni);
        }
    }
}
