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
        // Agregamos 'venta' a los tipos de adeudo permitidos y 'programado' a los estatus
        DB::statement("ALTER TABLE adeudos MODIFY COLUMN tipo ENUM('colegiatura', 'especial', 'venta') DEFAULT 'colegiatura'");
        DB::statement("ALTER TABLE adeudos MODIFY COLUMN status ENUM('pendiente', 'pagado', 'vencido', 'programado') DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE adeudos MODIFY COLUMN tipo ENUM('colegiatura', 'especial') DEFAULT 'colegiatura'");
        DB::statement("ALTER TABLE adeudos MODIFY COLUMN status ENUM('pendiente', 'pagado', 'vencido') DEFAULT 'pendiente'");
    }
};
