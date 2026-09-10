<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pago;
use App\Models\Gasto;
use App\Models\Corte;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CorteRetroactivo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'caja:corte-retroactivo 
                            {--fecha=2026-08-07 : Fecha limite para el corte retroactivo (Formato YYYY-MM-DD)} 
                            {--cajero=caja1 : Nombre, Email o ID del usuario/cajero a quien se asignara el corte} 
                            {--dry-run : Muestra la simulacion sin realizar cambios en la base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un corte de caja retroactivo asignado a un cajero específico para agrupar las ventas y gastos de prueba.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fechaStr = $this->option('fecha');
        $cajeroInput = $this->option('cajero');
        $isDryRun = $this->option('dry-run');

        try {
            $limitDate = Carbon::parse($fechaStr)->endOfDay();
        } catch (\Exception $e) {
            $this->error("La fecha proporcionada '{$fechaStr}' no es valida. Usa el formato YYYY-MM-DD.");
            return 1;
        }

        // Buscar el usuario del cajero especificado
        $cajeroUser = null;
        if (!empty($cajeroInput)) {
            $cajeroUser = User::where('name', $cajeroInput)
                ->orWhere('email', $cajeroInput)
                ->orWhere('id', $cajeroInput)
                ->first();

            if (!$cajeroUser) {
                $this->error("No se encontro ningun usuario/cajero con el identificador '{$cajeroInput}'.");
                return 1;
            }
        }

        $this->info("=================================================");
        $this->info("  CORTE DE CAJA RETROACTIVO / LIMPIEZA DE PRUEBAS ");
        $this->info("=================================================");
        $this->info("Fecha limite seleccionada: " . $limitDate->toDateTimeString());
        if ($cajeroUser) {
            $this->info("Cajero asignado para el corte: {$cajeroUser->name} (ID: {$cajeroUser->id}, Email: {$cajeroUser->email})");
        }
        if ($isDryRun) {
            $this->warn(">>> MODO SIMULACION (DRY-RUN) ACTIVO - No se modificara la base de datos <<<");
        }
        $this->newLine();

        // 1. Obtener pagos pendientes <= limite
        $pagosRetro = Pago::whereNull('corte_id')
            ->where('status', 'completado')
            ->where('fecha_pago', '<=', $limitDate)
            ->with('cajero')
            ->get();

        // 2. Obtener gastos pendientes <= limite
        $gastosRetro = Gasto::whereNull('corte_id')
            ->where('fecha', '<=', $limitDate->toDateString())
            ->with('cajero')
            ->get();

        // 3. Obtener pagos y gastos POSTERIORES (>= 08/08/2026) que quedaran activos en caja
        $pagosPosteriores = Pago::whereNull('corte_id')
            ->where('status', 'completado')
            ->where('fecha_pago', '>', $limitDate)
            ->get();

        $gastosPosteriores = Gasto::whereNull('corte_id')
            ->where('fecha', '>', $limitDate->toDateString())
            ->get();

        if ($pagosRetro->isEmpty() && $gastosRetro->isEmpty()) {
            $this->info("No se encontraron pagos ni gastos pendientes de corte anteriores o iguales al {$limitDate->toDateString()}.");
        } else {
            $this->info("Resumen de movimientos a CERRAR en Corte Retroactivo (<= {$limitDate->toDateString()}):");
            
            $totCobrado = $pagosRetro->sum('total');
            $totGastado = $gastosRetro->sum('monto');

            $tableData = [[
                'Cajero Asignado' => $cajeroUser ? $cajeroUser->name : 'Segun Usuario Original',
                'Tickets a Cerrar' => $pagosRetro->count(),
                'Total Cobrado' => '$' . number_format($totCobrado, 2),
                'Gastos a Cerrar' => $gastosRetro->count(),
                'Total Gastado' => '$' . number_format($totGastado, 2),
                'Neto Corte' => '$' . number_format($totCobrado - $totGastado, 2),
            ]];

            $this->table(
                ['Cajero Asignado', 'Tickets a Cerrar', 'Total Cobrado', 'Gastos a Cerrar', 'Total Gastado', 'Neto Corte'],
                $tableData
            );
        }

        $this->newLine();
        $this->info("=================================================");
        $this->info("Resumen de ventas/gastos que PERMANECERAN ACTIVOS en Caja (post {$limitDate->toDateString()}):");
        $this->info("- Tickets en caja activa (>= 08/08/2026): " . $pagosPosteriores->count() . " (Total: $" . number_format($pagosPosteriores->sum('total'), 2) . ")");
        $this->info("- Gastos en caja activa (>= 08/08/2026): " . $gastosPosteriores->count() . " (Total: $" . number_format($gastosPosteriores->sum('monto'), 2) . ")");
        $this->info("=================================================");

        if ($isDryRun) {
            $this->warn("Simulacion finalizada. Ejecuta el comando sin --dry-run para aplicar las modificaciones.");
            return 0;
        }

        if ($pagosRetro->isEmpty() && $gastosRetro->isEmpty()) {
            return 0;
        }

        $cajeroNombre = $cajeroUser ? $cajeroUser->name : 'sus usuarios correspondientes';
        if (!$this->confirm("Deseas proceder a crear el corte retroactivo a nombre de '{$cajeroNombre}' para los registros anteriores al {$limitDate->toDateString()}?", true)) {
            $this->comment("Operacion cancelada por el usuario.");
            return 0;
        }

        // Ejecutar transacción real
        DB::transaction(function () use ($pagosRetro, $gastosRetro, $limitDate, $cajeroUser) {
            $totCobrado = $pagosRetro->sum('total');
            $totGastado = $gastosRetro->sum('monto');

            // Determinar fecha_inicio
            $fechas = collect();
            if ($pagosRetro->isNotEmpty()) {
                $fechas->push($pagosRetro->min('fecha_pago'));
            }
            if ($gastosRetro->isNotEmpty()) {
                $fechas->push($gastosRetro->min('fecha'));
            }
            $fechaInicio = $fechas->min() ?: Carbon::parse('2026-01-01');

            $assignedUserId = $cajeroUser ? $cajeroUser->id : ($pagosRetro->first()?->user_id ?? 1);

            // Crear registro de corte asignado a la caja especificada
            $corte = Corte::create([
                'user_id' => $assignedUserId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $limitDate,
                'total_cobrado' => $totCobrado,
                'total_gastado' => $totGastado
            ]);

            // Asignar corte_id
            if ($pagosRetro->isNotEmpty()) {
                Pago::whereIn('id', $pagosRetro->pluck('id'))->update(['corte_id' => $corte->id]);
            }
            if ($gastosRetro->isNotEmpty()) {
                Gasto::whereIn('id', $gastosRetro->pluck('id'))->update(['corte_id' => $corte->id]);
            }

            $this->info("Corte retroactivo #{$corte->id} creado exitosamente para la caja / usuario: '{$cajeroUser->name}' (ID: {$assignedUserId})");
        });

        $this->info("Proceso completado con exito. La caja ha sido saneada al {$limitDate->toDateString()}.");
        return 0;
    }
}
