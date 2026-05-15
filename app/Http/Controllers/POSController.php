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
        // Incluir el monto calculado para colegiaturas
        foreach($adeudos as $adeudo) {
            $adeudo->monto_calculado = $adeudo->monto_calculado; // Usa el accessor
        }
        return response()->json($adeudos);
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'items' => 'required|array',
            'metodo' => 'required|in:pago_inmediato,cargar_adeudo'
        ]);

        return DB::transaction(function () use ($request) {
            $alumno = Alumno::find($request->alumno_id);
            $pago = null;
            $totalPagado = 0;

            if ($request->metodo === 'pago_inmediato') {
                $pago = Pago::create([
                    'alumno_id' => $alumno->id,
                    'user_id' => Auth::id(),
                    'total' => 0,
                    'referencia_ticket' => 'POS-' . time(),
                    'fecha_pago' => now()
                ]);
            }

            foreach ($request->items as $item) {
                $descuento = isset($item['descuento']) ? (float)$item['descuento'] : 0;

                if ($item['tipo'] === 'adeudo_existente') {
                    $adeudo = Adeudo::find($item['id']);
                    if ($request->metodo === 'pago_inmediato') {
                        $montoOriginal = $adeudo->monto_calculado;
                        $montoFinal = $montoOriginal - $descuento;
                        
                        $adeudo->update([
                            'status' => 'pagado',
                            'fecha_pago' => now(),
                            'monto_actual' => $montoFinal
                        ]);

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
                } else {
                    // Es un producto nuevo
                    $producto = Producto::find($item['id']);
                    $montoOriginal = $producto->precio * $item['cantidad'];
                    $montoFinal = $montoOriginal - $descuento;
                    
                    $adeudo = Adeudo::create([
                        'alumno_id' => $alumno->id,
                        'tipo' => 'venta',
                        'concepto' => $producto->nombre . ($item['cantidad'] > 1 ? " (x{$item['cantidad']})" : ""),
                        'monto_base' => $montoOriginal,
                        'monto_actual' => $montoFinal,
                        'status' => $request->metodo === 'pago_inmediato' ? 'pagado' : 'pendiente',
                        'fecha_pago' => $request->metodo === 'pago_inmediato' ? now() : null,
                    ]);

                    if ($request->metodo === 'pago_inmediato') {
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
                }
            }

            if ($pago) {
                $pago->update(['total' => $totalPagado]);
            }

            return response()->json([
                'success' => true,
                'pago_id' => $pago ? $pago->id : null,
                'message' => $request->metodo === 'pago_inmediato' ? 'Pago procesado exitosamente.' : 'Cargos añadidos a la cuenta del alumno.'
            ]);
        });
    }
}
