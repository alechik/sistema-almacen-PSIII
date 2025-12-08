<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Alimentos', 'descripcion' => 'Productos alimenticios y consumibles'],
            ['nombre' => 'Bebidas', 'descripcion' => 'Bebidas en general'],
            ['nombre' => 'Limpieza', 'descripcion' => 'Productos de limpieza y desinfección'],
            ['nombre' => 'Herramientas', 'descripcion' => 'Herramientas manuales y eléctricas'],
            ['nombre' => 'Electrónicos', 'descripcion' => 'Artículos y dispositivos electrónicos'],
            ['nombre' => 'Medicamentos', 'descripcion' => 'Productos farmacéuticos y medicinales'],
            ['nombre' => 'Ropa y Textiles', 'descripcion' => 'Prendas de vestir y textiles'],
            ['nombre' => 'Oficina', 'descripcion' => 'Material y suministros de oficina'],
            ['nombre' => 'Construcción', 'descripcion' => 'Materiales para construcción'],
            ['nombre' => 'Viveres', 'descripcion' => 'Productos no perecederos'],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(['nombre' => $cat['nombre']], $cat);
        }
    }
}
