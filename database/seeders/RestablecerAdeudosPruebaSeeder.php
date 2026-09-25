<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Alumno;
use App\Models\Adeudo;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\PagoInsuficiente;
use Illuminate\Support\Facades\DB;

class RestablecerAdeudosPruebaSeeder extends Seeder
{
    public function run()
    {
        $matriculas = ['26519', '26592'];

        foreach ($matriculas as $mat) {
            $alumno = Alumno::where('matricula', $mat)->first();

            if (!$alumno) {
                $alumno = Alumno::where('matricula', 'LIKE', "%{$mat}%")->first();
            }

            if (!$alumno) {
                $this->command->warn("Alumno no encontrado con matrícula: {$mat}");
                continue;
            }

            DB::transaction(function () use ($alumno, $mat) {
                // Obtener únicamente los IDs de adeudos de tipo colegiatura
                $adeudoIdsColegiatura = Adeudo::where('alumno_id', $alumno->id)
                    ->where('tipo', 'colegiatura')
                    ->pluck('id');

                if ($adeudoIdsColegiatura->isNotEmpty()) {
                    // 1. Obtener detalles de pago asociados EXCLUSIVAMENTE a adeudos de tipo colegiatura
                    $detallesColegiatura = PagoDetalle::whereIn('adeudo_id', $adeudoIdsColegiatura)->get();
                    $pagoIds = $detallesColegiatura->pluck('pago_id')->unique();

                    if ($detallesColegiatura->isNotEmpty()) {
                        // Eliminar solo los detalles de pago de colegiaturas
                        PagoDetalle::whereIn('id', $detallesColegiatura->pluck('id'))->delete();

                        // Eliminar pagos que ya no tienen más detalles asociados
                        foreach ($pagoIds as $pagoId) {
                            $detallesRestantes = PagoDetalle::where('pago_id', $pagoId)->count();
                            if ($detallesRestantes === 0) {
                                Pago::where('id', $pagoId)->delete();
                            }
                        }
                    }

                    // 2. Restablecer ÚNICAMENTE los adeudos de tipo 'colegiatura' a status = 'pendiente' y fecha_pago = null
                    Adeudo::whereIn('id', $adeudoIdsColegiatura)
                        ->update([
                            'status' => 'pendiente',
                            'fecha_pago' => null,
                        ]);
                }

                // 3. Limpiar registros en el reporte de saldo insuficiente para este alumno
                PagoInsuficiente::where('alumno_id', $alumno->id)
                    ->orWhere('matricula', $mat)
                    ->delete();
            });

            $this->command->info("Adeudos de COLEGIATURA restablecidos a 'pendiente' para la matrícula {$mat} ({$alumno->nombre_completo}). Ningún otro tipo de adeudo fue modificado.");
        }
    }
}
