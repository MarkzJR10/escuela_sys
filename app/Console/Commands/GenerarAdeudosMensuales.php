<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Alumno;
use App\Models\Adeudo;
use Carbon\Carbon;

class GenerarAdeudosMensuales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:adeudos {--dia= : Día a forzar (1 o 11)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera los adeudos mensuales de colegiatura y aplica recargos acumulativos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoy = Carbon::now();
        $periodoActual = $hoy->format('Y-m');
        $diaEjecucion = $this->option('dia') ?? $hoy->day;

        $this->info("Ejecutando actualización de adeudos para: " . $hoy->format('d/m/Y') . " (Día analizado: $diaEjecucion)");

        // REGLA DÍA 1: Iniciar mes y aplicar recargos a meses anteriores vencidos
        if ($diaEjecucion == 1) {
            // 1. Activar colegiaturas programadas del mes actual
            $activados = Adeudo::where('periodo', $periodoActual)
                ->where('status', 'programado')
                ->update(['status' => 'pendiente']);
            
            $this->info("Se activaron $activados colegiaturas para el nuevo mes.");

            // 2. Aplicar recargo acumulado (+10% del monto base) a todo lo que ya estaba VENCIDO antes de este mes
            $vencidosPrevios = Adeudo::where('status', 'vencido')
                ->where('periodo', '!=', $periodoActual)
                ->get();

            foreach ($vencidosPrevios as $adeudo) {
                $adeudo->update([
                    'monto_actual' => $adeudo->monto_actual + ($adeudo->monto_base * 0.10)
                ]);
            }
            $this->info("Se aplicó recargo acumulado a " . $vencidosPrevios->count() . " adeudos vencidos previos.");
        }

        // REGLA DÍA 11: Aplicar primer recargo al mes actual y pasarlo a Vencido
        if ($diaEjecucion == 11) {
            $adeudosMes = Adeudo::where('periodo', $periodoActual)
                ->where('status', 'pendiente')
                ->get();

            foreach ($adeudosMes as $adeudo) {
                $adeudo->update([
                    'status' => 'vencido',
                    'monto_actual' => $adeudo->monto_base * 1.10
                ]);
            }
            $this->info("Día 11: Se aplicó recargo inicial y cambio a Vencido para " . $adeudosMes->count() . " adeudos.");
        }

        $this->info("Proceso finalizado.");
    }
}
