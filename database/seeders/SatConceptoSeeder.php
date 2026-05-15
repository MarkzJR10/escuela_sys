<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SatConceptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conceptos = [
            ['clave' => '86121501', 'descripcion' => 'Prescolar'],
            ['clave' => '86121501', 'descripcion' => 'Maternal'],
            ['clave' => '86121503', 'descripcion' => 'Primaria'],
            ['clave' => '86121503', 'descripcion' => 'Secundaria'],
        ];

        foreach ($conceptos as $concepto) {
            \App\Models\SatConcepto::create($concepto);
        }
    }
}
