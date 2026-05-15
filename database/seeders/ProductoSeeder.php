<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            ['nombre' => 'Lápiz HB', 'precio' => 5.00, 'clave_sat' => '44121706'],
            ['nombre' => 'Goma de borrar', 'precio' => 8.50, 'clave_sat' => '44121804'],
            ['nombre' => 'Cuaderno Profesional', 'precio' => 35.00, 'clave_sat' => '44111507'],
            ['nombre' => 'Sacapuntas', 'precio' => 4.00, 'clave_sat' => '44121618'],
            ['nombre' => 'Uniforme Diario (Pants)', 'precio' => 450.00, 'clave_sat' => '53102700'],
            ['nombre' => 'Playera Polo Uniforme', 'precio' => 180.00, 'clave_sat' => '53102700'],
            ['nombre' => 'Seguro Escolar Anual', 'precio' => 250.00, 'clave_sat' => '84131500'],
        ];

        foreach ($productos as $p) {
            Producto::create($p);
        }
    }
}
