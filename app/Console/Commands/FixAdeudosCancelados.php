<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Adeudo;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class FixAdeudosCancelados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adeudos:fix-cancelados {--dry-run : Muestra los adeudos a corregir sin modificar la base de datos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige los adeudos de ventas directas de productos que quedaron pendientes al cancelar un ticket';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info("Iniciando escaneo de adeudos de ventas canceladas...");
        if ($dryRun) {
            $this->warn("Modo --dry-run activado: No se realizarán cambios en la base de datos.\n");
        }

        $afectados = Adeudo::with(['pagosDetalles.pago', 'alumno'])
            ->where('tipo', 'venta')
            ->where('status', 'pendiente')
            ->whereHas('pagosDetalles.pago', function ($q) {
                $q->where('status', 'cancelado');
            })
            ->get();

        $corregidos = 0;

        foreach ($afectados as $adeudo) {
            foreach ($adeudo->pagosDetalles as $detalle) {
                $pago = $detalle->pago;
                if ($pago && $pago->status === 'cancelado') {
                    $diff = ($adeudo->created_at && $pago->created_at) ? abs($adeudo->created_at->diffInSeconds($pago->created_at)) : 99999;
                    
                    if ($diff < 120) {
                        $corregidos++;
                        $this->line(sprintf(
                            "[%d] Adeudo ID: %d | Concepto: '%s' | Alumno: %s %s | Ticket Cancelado #%06d",
                            $corregidos,
                            $adeudo->id,
                            $adeudo->concepto,
                            $adeudo->alumno ? $adeudo->alumno->nombre : 'N/A',
                            $adeudo->alumno ? $adeudo->alumno->apellido_paterno : '',
                            $pago->id
                        ));

                        if (!$dryRun) {
                            DB::transaction(function () use ($adeudo) {
                                $adeudo->status = 'cancelado';
                                $adeudo->fecha_pago = null;
                                $adeudo->save();

                                // Intentar restaurar stock del producto
                                $concepto = $adeudo->concepto;
                                $cantidad = 1;
                                $nombreProducto = $concepto;

                                if (preg_match('/^(.*)\s+\(x(\d+)\)$/', $concepto, $matches)) {
                                    $nombreProducto = trim($matches[1]);
                                    $cantidad = (int) $matches[2];
                                }

                                $producto = Producto::where('nombre', $nombreProducto)->first();
                                if ($producto) {
                                    $producto->increment('stock', $cantidad);
                                    $this->info("    --> Estatus cambiado a 'cancelado'. Restauradas {$cantidad} unidad(es) al producto '{$nombreProducto}' (Stock: {$producto->stock}).");
                                } else {
                                    $this->info("    --> Estatus cambiado a 'cancelado'.");
                                }
                            });
                        }
                    }
                }
            }
        }

        if ($corregidos === 0) {
            $this->info("No se encontraron adeudos pendientes de ventas canceladas por corregir.");
        } else {
            if ($dryRun) {
                $this->warn("\nSe encontraron {$corregidos} adeudos. Ejecute sin '--dry-run' para aplicar los cambios.");
            } else {
                $this->info("\nSe corrigieron exitosamente {$corregidos} adeudos.");
            }
        }

        return Command::SUCCESS;
    }
}
