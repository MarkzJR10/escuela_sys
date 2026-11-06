<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('conceptos_adeudos_especiales')) {
            Schema::create('conceptos_adeudos_especiales', function (Blueprint $table) {
                $table->id();
                $table->string('nombre')->unique();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        // Precargar todos los conceptos de adeudos especiales existentes en la tabla adeudos
        if (Schema::hasTable('adeudos')) {
            $conceptosExistentes = DB::table('adeudos')
                ->where('tipo', 'especial')
                ->whereNotNull('concepto')
                ->where('concepto', '!=', '')
                ->select('concepto')
                ->distinct()
                ->pluck('concepto');

            $now = now();
            foreach ($conceptosExistentes as $concepto) {
                $nombre = trim($concepto);
                if ($nombre === '') {
                    continue;
                }

                $exists = DB::table('conceptos_adeudos_especiales')
                    ->where('nombre', $nombre)
                    ->exists();

                if (!$exists) {
                    DB::table('conceptos_adeudos_especiales')->insert([
                        'nombre' => $nombre,
                        'activo' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos_adeudos_especiales');
    }
};
