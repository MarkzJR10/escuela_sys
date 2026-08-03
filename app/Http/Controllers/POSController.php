<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Producto;
use App\Models\Adeudo;
use App\Models\Pago;
use App\Models\PagoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller
{
    public function index()
    {
        $productos = Producto::where('activo', true)->get();
        return view('pos.index', compact('productos'));
    }

    public function buscarAlumno(Request $request)
    {
        $term = $request->term;
        $alumnos = Alumno::where('nombre', 'LIKE', "%$term%")
            ->orWhere('apellido_paterno', 'LIKE', "%$term%")
            ->orWhere('matricula', 'LIKE', "%$term%")
            ->with('gradoGrupo')
            ->get();

        return response()->json($alumnos);
    }

    public function getAdeudos(Alumno $alumno)
    {
        $adeudos = $alumno->adeudos()->whereIn('status', ['pendiente', 'vencido', 'programado'])->get();
        // Incluir el monto calculado y forzar el concepto
        foreach($adeudos as $adeudo) {
            $adeudo->monto_calculado = $adeudo->monto_calculado; // Usa el accessor
            $adeudo->concepto = $adeudo->concepto; // Forza el fallback
        }
        return response()->json($adeudos);
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'items' => 'required|array',
            'metodo_pago' => 'required|in:efectivo,tarjeta,credito'
        ]);

        return DB::transaction(function () use ($request) {
            $alumno = Alumno::find($request->alumno_id);
            $pago = null;
            $totalPagado = 0;
            $isPagoInmediato = in_array($request->metodo_pago, ['efectivo', 'tarjeta']);

            if ($isPagoInmediato) {
                $pago = Pago::create([
                    'alumno_id' => $alumno->id,
                    'user_id' => Auth::id(),
                    'total' => 0,
                    'metodo_pago' => $request->metodo_pago,
                    'referencia_ticket' => 'POS-' . time(),
                    'fecha_pago' => now()
                ]);
            }

            foreach ($request->items as $item) {
                $descuento = isset($item['descuento']) ? (float)$item['descuento'] : 0;

                if ($item['tipo'] === 'adeudo_existente') {
                    $adeudo = Adeudo::find($item['id']);
                    if ($isPagoInmediato) {
                        $montoOriginal = $adeudo->monto_calculado;
                        
                        // Si se especificó un monto de abono, usamos ese valor, de lo contrario liquidamos completo
                        $montoPagado = isset($item['monto_pagar']) ? (float)$item['monto_pagar'] : ($montoOriginal - $descuento);
                        if ($montoPagado > ($montoOriginal - $descuento)) {
                            $montoPagado = $montoOriginal - $descuento;
                        }
                        if ($montoPagado < 0) {
                            $montoPagado = 0;
                        }
                        
                        // Monto que queda pendiente tras restar el abono y el descuento
                        $montoRestante = $montoOriginal - $montoPagado - $descuento;
                        if ($montoRestante <= 0.01) {
                            // Liquidado por completo
                            $adeudo->update([
                                'status' => 'pagado',
                                'fecha_pago' => now(),
                                'monto_actual' => 0
                            ]);
                        } else {
                            // Abono parcial: disminuye el monto_actual pero sigue pendiente
                            $adeudo->update([
                                'monto_actual' => $montoRestante
                                // status no se actualiza a pagado
                            ]);
                        }

                        PagoDetalle::create([
                            'pago_id' => $pago->id,
                            'adeudo_id' => $adeudo->id,
                            'monto_adeudo' => $montoOriginal,
                            'descuento' => $descuento,
                            'monto_pagado' => $montoPagado,
                            'notas' => $montoRestante > 0.01 
                                ? "Abono parcial. Restante: $" . number_format($montoRestante, 2)
                                : ($descuento > 0 ? "Descuento aplicado en caja" : null)
                        ]);
                        $totalPagado += $montoPagado;
                    }
                } else {
                    // Es un producto nuevo
                    $producto = Producto::find($item['id']);
                    
                    if ($producto->stock < $item['cantidad']) {
                        throw new \Exception("No hay stock suficiente para: " . $producto->nombre);
                    }

                    $montoOriginal = $producto->precio * $item['cantidad'];
                    $montoFinal = $montoOriginal - $descuento;
                    
                    $adeudo = Adeudo::create([
                        'alumno_id' => $alumno->id,
                        'tipo' => 'venta',
                        'concepto' => $producto->nombre . ($item['cantidad'] > 1 ? " (x{$item['cantidad']})" : ""),
                        'monto_base' => $montoOriginal,
                        'monto_actual' => $montoFinal,
                        'status' => $isPagoInmediato ? 'pagado' : 'pendiente',
                        'fecha_pago' => $isPagoInmediato ? now() : null,
                    ]);

                    if ($isPagoInmediato) {
                        PagoDetalle::create([
                            'pago_id' => $pago->id,
                            'adeudo_id' => $adeudo->id,
                            'monto_adeudo' => $montoOriginal,
                            'descuento' => $descuento,
                            'monto_pagado' => $montoFinal,
                            'notas' => $descuento > 0 ? "Descuento aplicado en caja" : null
                        ]);
                        $totalPagado += $montoFinal;
                    }

                    // Decrementar el stock
                    if ($producto->stock > 0) {
                        $producto->decrement('stock', $item['cantidad']);
                    }
                }
            }

            if ($pago) {
                $pago->update(['total' => $totalPagado]);
            }

            return response()->json([
                'success' => true,
                'pago_id' => $pago ? $pago->id : null,
                'message' => $isPagoInmediato ? 'Pago procesado exitosamente.' : 'Cargos añadidos a la cuenta del alumno (A Crédito).'
            ]);
        });
    }
}
