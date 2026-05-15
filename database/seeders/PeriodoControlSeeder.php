<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PeriodoControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([1, 2, 3] as $t) {
            \App\Models\PeriodoControl::firstOrCreate(
                ['trimestre' => $t],
                ['activo' => true]
            );
        }
    }
}
