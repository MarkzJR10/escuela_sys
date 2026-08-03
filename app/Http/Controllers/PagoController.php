<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Adeudo;
use App\Models\Pago;
use App\Models\PagoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PagoController extends Controller
{
    /**
     * Muestra el buscador de alumnos para iniciar un cobro.
     */
    public function index(Request $request)
    {
        $alumnos = Alumno::with('gradoGrupo')->get();

        return view('pagos.index', compact('alumnos'));
    }

    /**
     * Muestra la pantalla de caja para un alumno específico.
     */
    public function create(Alumno $alumno)
    {
        $adeudos = $alumno->adeudos()->whereIn('status', ['pendiente', 'vencido'])->get();
        return view('pagos.create', compact('alumno', 'adeudos'));
    }

    /**
     * Procesa el pago y genera el ticket.
     */
    public function store(Request $request, Alumno $alumno)
    {
        $request->validate([
            'adeudo_ids' => 'required|array',
            'adeudo_ids.*' => 'exists:adeudos,id',
            'descuentos' => 'nullable|array',
            'metodo_pago' => 'required|in:efectivo,tarjeta,transferencia',
        ]);

        return DB::transaction(function () use ($request, $alumno) {
            $totalPagado = 0;
            $referencia = 'TKT-' . strtoupper(Str::random(5)) . '-' . rand(1000, 9999);

            $pago = Pago::create([
                'alumno_id' => $alumno->id,
                'user_id' => auth()->id(),
                'total' => 0, // Se actualizará al final
                'metodo_pago' => $request->metodo_pago,
                'referencia_ticket' => $referencia,
                'fecha_pago' => now(),
            ]);

            foreach ($request->adeudo_ids as $adeudoId) {
                $adeudo = Adeudo::findOrFail($adeudoId);
                $montoDebido = $adeudo->monto_calculado; // Usamos el monto calculado con recargos si aplica
                $descuento = floatval($request->descuentos[$adeudoId] ?? 0);
                $montoFinal = $montoDebido - $descuento;

                if ($montoFinal < 0) $montoFinal = 0;

                $nota = null;
                if ($descuento > 0) {
                    $nota = "Se aplicó $" . number_format($descuento, 2) . " de descuento sobre " . ($adeudo->concepto ?? 'Colegiatura');
                }

                PagoDetalle::create([
                    'pago_id' => $pago->id,
                    'adeudo_id' => $adeudo->id,
                    'monto_adeudo' => $montoDebido,
                    'descuento' => $descuento,
                    'monto_pagado' => $montoFinal,
                    'notas' => $nota,
                ]);

                // Actualizar estado del adeudo
                $adeudo->update(['status' => 'pagado']);
                $totalPagado += $montoFinal;
            }

            $pago->update(['total' => $totalPagado]);

            return redirect()->route('pagos.ticket', $pago->id)
                ->with('success', 'Pago procesado exitosamente.');
        });
    }

    /**
     * Muestra el ticket generado.
     */
    public function ticket(Pago $pago)
    {
        $pago->load(['alumno.gradoGrupo', 'cajero', 'detalles.adeudo']);
        return view('pagos.ticket', compact('pago'));
    }

    /**
     * Reporte para el contador.
     */
    public function reporte(Request $request)
    {
        $pagos = Pago::with(['alumno', 'cajero', 'detalles'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('contabilidad.ventas', compact('pagos'));
    }
}
