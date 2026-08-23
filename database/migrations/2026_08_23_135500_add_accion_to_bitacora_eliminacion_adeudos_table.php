<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bitacora_eliminacion_adeudos', function (Blueprint $table) {
            $table->string('accion', 100)->default('Eliminación de Adeudo')->after('ciclo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacora_eliminacion_adeudos', function (Blueprint $table) {
            $table->dropColumn('accion');
        });
    }
};
