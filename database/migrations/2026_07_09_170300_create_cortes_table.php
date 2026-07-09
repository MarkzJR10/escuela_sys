<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // cajero
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->decimal('total_cobrado', 10, 2)->default(0);
            $table->decimal('total_gastado', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('corte_id')->nullable()->constrained('cortes')->onDelete('set null');
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->foreignId('corte_id')->nullable()->constrained('cortes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('gastos', function (Blueprint $table) {
            $table->dropForeign(['corte_id']);
            $table->dropColumn('corte_id');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['corte_id']);
            $table->dropColumn('corte_id');
        });

        Schema::dropIfExists('cortes');
    }
};
