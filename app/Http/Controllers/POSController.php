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
        $term = trim($request->term);
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $results = [];

        // 1. Buscar Alumnos
        $alumnos = Alumno::where('nombre', 'LIKE', "%$term%")
            ->orWhere('apellido_paterno', 'LIKE', "%$term%")
            ->orWhere('apellido_materno', 'LIKE', "%$term%")
            ->orWhere('matricula', 'LIKE', "%$term%")
            ->with(['gradoGrupo', 'padre'])
            ->get();

        foreach ($alumnos as $al) {
            $results[] = [
                'tipo' => 'alumno',
                'id' => $al->id,
                'nombre' => $al->nombre . ' ' . $al->apellido_paterno . ' ' . ($al->apellido_materno ?? ''),
                'info' => 'Matrícula: ' . $al->matricula . ' | ' . ($al->gradoGrupo ? $al->gradoGrupo->grado . ' ' . $al->gradoGrupo->grupo : 'Sin Grado'),
                'raw' => $al
            ];
        }

        // 2. Buscar Padres (Tutores)
        $padres = \App\Models\Padre::where('nombre', 'LIKE', "%$term%")
            ->orWhere('apellido_paterno', 'LIKE', "%$term%")
            ->orWhere('apellido_materno', 'LIKE', "%$term%")
            ->with('alumnos.gradoGrupo')
            ->get();

        foreach ($padres as $p) {
            $hijosNombres = $p->alumnos->map(fn($h) => $h->nombre . ' (' . ($h->gradoGrupo ? $h->gradoGrupo->grado . $h->gradoGrupo->grupo : 'S/G') . ')')->join(', ');
            $results[] = [
                'tipo' => 'padre',
                'id' => $p->id,
                'nombre' => 'Padre/Tutor: ' . $p->nombre . ' ' . $p->apellido_paterno . ' ' . ($p->apellido_materno ?? ''),
                'info' => 'Hijos: ' . ($hijosNombres ?: 'Sin hijos vinculados'),
                'raw' => $p
            ];
        }

        return response()->json($results);
    }

    public function getAdeudos(Request $request, $id)
    {
        $tipo = $request->get('tipo_cliente', 'alumno');
        $adeudosList = [];

        if ($tipo === 'padre') {
            $padre = \App\Models\Padre::with(['alumnos.adeudos' => function ($q) {
                $q->whereIn('status', ['pendiente', 'vencido', 'programado']);
            }, 'alumnos.gradoGrupo'])->find($id);

            if ($padre) {
                foreach ($padre->alumnos as $hijo) {
                    foreach ($hijo->adeudos as $adeudo) {
                        $adeudo->monto_calculado = $adeudo->monto_calculado;
                        $adeudo->concepto = $adeudo->concepto;
                        $adeudo->alumno_nombre = $hijo->nombre . ' ' . $hijo->apellido_paterno;
                        $adeudo->alumno_id = $hijo->id;
                        $adeudosList[] = $adeudo;
                    }
                }
            }
        } else {
            $alumno = Alumno::find($id);
            if ($alumno) {
                $adeudos = $alumno->adeudos()->whereIn('status', ['pendiente', 'vencido', 'programado'])->get();
                foreach ($adeudos as $adeudo) {
                    $adeudo->monto_calculado = $adeudo->monto_calculado;
                    $adeudo->concepto = $adeudo->concepto;
                    $adeudo->alumno_nombre = $alumno->nombre . ' ' . $alumno->apellido_paterno;
                    $adeudo->alumno_id = $alumno->id;
                    $adeudosList[] = $adeudo;
                }
            }
        }

        return response()->json($adeudosList);
    }

    public function procesar(Request $request)
    {
        $request->validate([
            'tipo_cliente' => 'required|in:alumno,padre',
            'cliente_id' => 'required|integer',
            'items' => 'required|array',
            'metodo_pago' => 'required|in:efectivo,tarjeta,credito'
        ]);

        return DB::transaction(function () use ($request) {
            $tipoCliente = $request->tipo_cliente;
            $clienteId = $request->cliente_id;
            
            // Determinar el alumno primario para asociar la cabecera del Pago
            $alumnoPrincipalId = null;
            $alumnosDelPadre = collect();

            if ($tipoCliente === 'padre') {
                $padre = \App\Models\Padre::with('alumnos')->find($clienteId);
                $alumnosDelPadre = $padre ? $padre->alumnos : collect();
                if ($alumnosDelPadre->isNotEmpty()) {
                    $alumnoPrincipalId = $alumnosDelPadre->first()->id;
                }
            } else {
                $alumnoPrincipalId = $clienteId;
            }

            if (!$alumnoPrincipalId) {
                throw new \Exception("No se encontró un alumno válido para registrar el ticket.");
            }

            $pago = null;
            $totalPagado = 0;
            $isPagoInmediato = in_array($request->metodo_pago, ['efectivo', 'tarjeta']);

            if ($isPagoInmediato) {
                $pago = Pago::create([
                    'alumno_id' => $alumnoPrincipalId,
                    'user_id' => Auth::id(),
                    'total' => 0,
                    'metodo_pago' => $request->metodo_pago,
                    'referencia_ticket' => 'POS-' . time(),
                    'fecha_pago' => now()
                ]);
            }

            foreach ($request->items as $item) {
                $descuento = isset($item['descuento']) ? (float)$item['descuento'] : 0;
                $itemAlumnoId = isset($item['alumno_id']) && !empty($item['alumno_id']) 
                    ? $item['alumno_id'] 
                    : $alumnoPrincipalId;

                if ($item['tipo'] === 'adeudo_existente') {
                    $adeudo = Adeudo::find($item['id']);
                    if ($isPagoInmediato && $adeudo) {
                        $montoOriginal = $adeudo->monto_calculado;
                        
                        $montoPagado = isset($item['monto_pagar']) ? (float)$item['monto_pagar'] : ($montoOriginal - $descuento);
                        if ($montoPagado > ($montoOriginal - $descuento)) {
                            $montoPagado = $montoOriginal - $descuento;
                        }
                        if ($montoPagado < 0) {
                            $montoPagado = 0;
                        }
                        
                        $montoRestante = $montoOriginal - $montoPagado - $descuento;
                        if ($montoRestante <= 0.01) {
                            $adeudo->update([
                                'status' => 'pagado',
                                'fecha_pago' => now(),
                                'monto_actual' => 0
                            ]);
                        } else {
                            $adeudo->update([
                                'monto_actual' => $montoRestante
                            ]);
                        }

                        $notasCustom = isset($item['notas']) ? trim($item['notas']) : null;
                        $alumnoObj = Alumno::find($adeudo->alumno_id);
                        $tagAlumno = ($tipoCliente === 'padre' && $alumnoObj) ? "Alumno: {$alumnoObj->nombre} {$alumnoObj->apellido_paterno}" : null;
                        
                        $notasAuto = $montoRestante > 0.01 
                            ? "Abono parcial. Restante: $" . number_format($montoRestante, 2)
                            : ($descuento > 0 ? "Descuento aplicado en caja" : null);

                        $notasFinales = implode(' | ', array_filter([$tagAlumno, $notasCustom, $notasAuto]));

                        PagoDetalle::create([
                            'pago_id' => $pago->id,
                            'adeudo_id' => $adeudo->id,
                            'monto_adeudo' => $montoOriginal,
                            'descuento' => $descuento,
                            'monto_pagado' => $montoPagado,
                            'notas' => $notasFinales ?: null
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
                    $notasCustom = isset($item['notas']) ? trim($item['notas']) : null;
                    
                    $alumnoObj = Alumno::find($itemAlumnoId);
                    $tagAlumno = ($tipoCliente === 'padre' && $alumnoObj) ? "Alumno: {$alumnoObj->nombre} {$alumnoObj->apellido_paterno}" : null;

                    $adeudo = Adeudo::create([
                        'alumno_id' => $itemAlumnoId,
                        'tipo' => 'venta',
                        'concepto' => $producto->nombre . ($item['cantidad'] > 1 ? " (x{$item['cantidad']})" : ""),
                        'monto_base' => $montoOriginal,
                        'monto_actual' => $montoFinal,
                        'status' => $isPagoInmediato ? 'pagado' : 'pendiente',
                        'fecha_pago' => $isPagoInmediato ? now() : null,
                    ]);

                    if ($isPagoInmediato) {
                        $notasAuto = $descuento > 0 ? "Descuento aplicado en caja" : null;
                        $notasFinales = implode(' | ', array_filter([$tagAlumno, $notasCustom, $notasAuto]));

                        PagoDetalle::create([
                            'pago_id' => $pago->id,
                            'adeudo_id' => $adeudo->id,
                            'monto_adeudo' => $montoOriginal,
                            'descuento' => $descuento,
                            'monto_pagado' => $montoFinal,
                            'notas' => $notasFinales ?: null
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
