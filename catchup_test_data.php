<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Adeudo;
use Carbon\Carbon;

$hoy = Carbon::now();
$periodoActual = $hoy->format('Y-m');

echo "Actualizando adeudos de prueba a la fecha real: " . $hoy->format('d/m/Y') . "\n";

$adeudos = Adeudo::where('concepto', 'Colegiatura Mensual')->get();

foreach ($adeudos as $adeudo) {
    $periodo = $adeudo->periodo;
    $fechaPeriodo = Carbon::parse($periodo . '-01');
    
    if ($periodo < $periodoActual) {
        // MESES PASADOS
        $status = 'vencido';
        // Base + 10% (día 11 del mes original)
        $monto = $adeudo->monto_base * 1.10;
        
        // +10% acumulado por cada mes que ha pasado desde entonces hasta el 1 de Mayo
        $mesesTranscurridos = $fechaPeriodo->diffInMonths($hoy);
        
        for ($i = 1; $i <= $mesesTranscurridos; $i++) {
            $monto *= 1.10;
        }
        
        $adeudo->update([
            'status' => $status,
            'monto_actual' => $monto
        ]);
    } elseif ($periodo === $periodoActual) {
        // MES ACTUAL (Mayo 2026)
        if ($hoy->day >= 11) {
            $adeudo->update([
                'status' => 'vencido',
                'monto_actual' => $adeudo->monto_base * 1.10
            ]);
        } else {
            $adeudo->update([
                'status' => 'pendiente',
                'monto_actual' => $adeudo->monto_base
            ]);
        }
    } else {
        // MESES FUTUROS (Si los hubiera)
        $adeudo->update([
            'status' => 'programado',
            'monto_actual' => $adeudo->monto_base
        ]);
    }
}

echo "Actualización completada.\n";
