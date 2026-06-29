<?php

namespace App\Imports;

use App\Models\Adeudo;
use App\Models\Alumno;
use App\Models\Pago;
use App\Models\PagoDetalle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class PagosImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // row: matricula, monto
        if (!isset($row['matricula']) || !isset($row['monto'])) {
            return null; // Omitir fila inválida
        }

        $alumno = Alumno::where('matricula', $row['matricula'])->first();
        if (!$alumno) {
            return null; // Omitir si no existe el alumno
        }

        $montoPago = (float) $row['monto'];
        if ($montoPago <= 0) {
            return null;
        }

        // Buscar el adeudo más antiguo pendiente
        $adeudo = Adeudo::where('alumno_id', $alumno->id)
                        ->where('status', 'pendiente')
                        ->orderBy('fecha_vencimiento', 'asc')
                        ->first();

        if ($adeudo) {
            DB::transaction(function () use ($alumno, $adeudo, $montoPago) {
                // Registrar Pago
                $pago = Pago::create([
                    'alumno_id' => $alumno->id,
                    'user_id' => Auth::id() ?? 1,
                    'total' => $montoPago,
                    'metodo_pago' => 'transferencia', // Por defecto para cargas en excel
                    'referencia_ticket' => 'EXCEL-' . strtoupper(uniqid()),
                    'fecha_pago' => now(),
                    'status' => 'completado'
                ]);

                // Registrar Detalle
                PagoDetalle::create([
                    'pago_id' => $pago->id,
                    'adeudo_id' => $adeudo->id,
                    'concepto' => $adeudo->concepto . ' (' . $adeudo->periodo . ')',
                    'monto_pagado' => $montoPago,
                ]);

                // Actualizar adeudo
                // Si el pago cubre el monto exacto (simplificado para importaciones)
                $adeudo->update([
                    'status' => 'pagado',
                    'fecha_pago' => now()->toDateString(),
                ]);
            });
        }

        return null; // ToModel doesn't require saving if handled manually
    }
}
