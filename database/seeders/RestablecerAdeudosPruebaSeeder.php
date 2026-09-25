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
                // 1. Obtener los IDs de pagos del alumno
                $pagoIds = Pago::where('alumno_id', $alumno->id)->pluck('id');

                if ($pagoIds->isNotEmpty()) {
                    // Eliminar detalles de pago
                    PagoDetalle::whereIn('pago_id', $pagoIds)->delete();

                    // Eliminar registros de pago
                    Pago::whereIn('id', $pagoIds)->delete();
                }

                // 2. Restablecer los adeudos a status = 'pendiente' y fecha_pago = null
                Adeudo::where('alumno_id', $alumno->id)
                    ->update([
                        'status' => 'pendiente',
                        'fecha_pago' => null,
                    ]);

                // 3. Limpiar cualquier registro en el reporte de saldo insuficiente
                PagoInsuficiente::where('alumno_id', $alumno->id)
                    ->orWhere('matricula', $mat)
                    ->delete();
            });

            $this->command->info("Adeudos restablecidos a 'pendiente' y pagos de prueba eliminados para la matrícula {$mat} ({$alumno->nombre_completo}).");
        }
    }
}
