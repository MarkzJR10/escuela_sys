<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\Adeudo;
use App\Models\Gasto;
use App\Models\Discrepancia;
use App\Models\User;
use App\Models\Corte;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContabilidadController extends Controller
{
    /**
     * Lista de Ventas (Tickets Completados) con opción a cancelar
     */
    public function listaVentas(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        
        $pagos = Pago::with(['alumno', 'cajero', 'detalles.adeudo'])
            ->whereDate('fecha_pago', $fecha)
            ->where('status', 'completado')
            ->orderBy('id', 'desc')
            ->get();

        return view('contabilidad.lista_ventas', compact('pagos', 'fecha'));
    }

    /**
     * Cancelar un ticket de venta
     */
    public function cancelarTicket(Request $request, Pago $pago)
    {
        DB::transaction(function () use ($pago) {
            // Marcar el pago como cancelado
            $pago->update(['status' => 'cancelado']);

            // Revertir los adeudos a pendiente
            foreach ($pago->detalles as $detalle) {
                if ($detalle->adeudo_id) {
                    $adeudo = Adeudo::find($detalle->adeudo_id);
                    if ($adeudo) {
                        $adeudo->update(['status' => 'pendiente', 'fecha_pago' => null]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Ticket #'.$pago->id.' cancelado exitosamente. Los adeudos han vuelto a estado pendiente.');
    }

    /**
     * Reporte de Ventas Canceladas
     */
    public function ventasCanceladas(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        
        $pagos = Pago::with(['alumno', 'cajero', 'detalles'])
            ->whereDate('fecha_pago', $fecha)
            ->where('status', 'cancelado')
            ->orderBy('updated_at', 'desc') // fecha de cancelación
            ->get();

        return view('contabilidad.ventas_canceladas', compact('pagos', 'fecha'));
    }

    /**
     * Reporte de Ventas por Fecha (Rango)
     */
    public function ventasPorFecha(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->toDateString());
        $fechaFin = $request->input('fecha_fin', now()->toDateString());

        $pagos = Pago::with(['cajero'])
            ->whereBetween(DB::raw('DATE(fecha_pago)'), [$fechaInicio, $fechaFin])
            ->where('status', 'completado')
            ->get();

        $total = $pagos->sum('total');

        return view('contabilidad.ventas_por_fecha', compact('pagos', 'fechaInicio', 'fechaFin', 'total'));
    }

    /**
     * Reporte de Ventas por Producto x Fecha
     */
    public function ventasProductoFecha(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());

        // Obtener detalles de productos vendidos (adeudos de tipo venta_credito o venta directa ligados a pagos de hoy)
        $detalles = PagoDetalle::join('adeudos', 'pago_detalles.adeudo_id', '=', 'adeudos.id')
            ->whereHas('pago', function($q) use ($fecha) {
                $q->whereDate('fecha_pago', $fecha)->where('status', 'completado');
            })
            ->select('adeudos.concepto', DB::raw('SUM(pago_detalles.monto_pagado) as total_vendido'), DB::raw('COUNT(*) as cantidad_tickets'))
            ->groupBy('adeudos.concepto')
            ->get();

        return view('contabilidad.ventas_producto', compact('detalles', 'fecha'));
    }

    /**
     * Reporte de Efectivo (Corte de Caja) por Cajero
     */
    public function efectivoCajas(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());

        $cajas = Pago::whereDate('fecha_pago', $fecha)
            ->where('status', 'completado')
            ->select('user_id', DB::raw('SUM(total) as total_cobrado'), DB::raw('COUNT(*) as total_tickets'))
            ->groupBy('user_id')
            ->with('cajero')
            ->get();

        return view('contabilidad.efectivo_cajas', compact('cajas', 'fecha'));
    }

    /**
     * Discrepancias (Faltantes/Sobrantes)
     */
    public function discrepancias(Request $request)
    {
        $mes = $request->input('mes', now()->format('Y-m'));

        $discrepancias = Discrepancia::with('cajero')
            ->where(DB::raw("DATE_FORMAT(fecha, '%Y-%m')"), $mes)
            ->orderBy('fecha', 'desc')
            ->get();

        return view('contabilidad.discrepancias.index', compact('discrepancias', 'mes'));
    }

    public function storeDiscrepancia(Request $request)
    {
        $request->validate([
            'monto_sistema' => 'required|numeric',
            'monto_fisico' => 'required|numeric',
            'motivo' => 'nullable|string'
        ]);

        $diferencia = $request->monto_fisico - $request->monto_sistema;

        Discrepancia::create([
            'user_id' => Auth::id(),
            'monto_sistema' => $request->monto_sistema,
            'monto_fisico' => $request->monto_fisico,
            'diferencia' => $diferencia,
            'motivo' => $request->motivo,
            'fecha' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'Discrepancia registrada correctamente.');
    }

    /**
     * Gastos (Por Cajero y Otros Gastos)
     */
    public function gastos(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());
        $gastos = Gasto::with('cajero')->whereDate('fecha', $fecha)->get();
        $totalGastos = $gastos->sum('monto');

        return view('contabilidad.gastos.index', compact('gastos', 'fecha', 'totalGastos'));
    }

    public function storeGasto(Request $request)
    {
        $request->validate([
            'concepto' => 'required|string',
            'monto' => 'required|numeric|min:0.1',
            'observaciones' => 'nullable|string'
        ]);

        Gasto::create([
            'user_id' => Auth::id(),
            'concepto' => $request->concepto,
            'monto' => $request->monto,
            'fecha' => now()->toDateString(),
            'observaciones' => $request->observaciones
        ]);

        return redirect()->back()->with('success', 'Gasto registrado correctamente.');
    }

    /**
     * Pantalla de Corte de Caja
     */
    public function corteCaja(Request $request)
    {
        $userId = Auth::id();

        // 1. Cobros pendientes (pagos sin corte asignado)
        $pagosPendientes = Pago::where('user_id', $userId)
            ->whereNull('corte_id')
            ->where('status', 'completado')
            ->get();
        $totalCobrado = $pagosPendientes->sum('total');

        // 2. Gastos pendientes (gastos sin corte asignado)
        $gastosPendientes = Gasto::where('user_id', $userId)
            ->whereNull('corte_id')
            ->get();
        $totalGastado = $gastosPendientes->sum('monto');

        // 3. Historial de cortes (cada cajero ve solo sus propios cortes)
        $cortes = Corte::where('user_id', $userId)->with('cajero')->orderBy('id', 'desc')->get();

        return view('contabilidad.corte_caja', compact('totalCobrado', 'totalGastado', 'cortes', 'pagosPendientes', 'gastosPendientes'));
    }

    /**
     * Procesar/Generar Corte de Caja
     */
    public function storeCorteCaja(Request $request)
    {
        try {
            $corte = DB::transaction(function() {
                $userId = Auth::id();
                
                // 1. Obtener transacciones pendientes
                $pagosPendientes = Pago::where('user_id', $userId)
                    ->whereNull('corte_id')
                    ->where('status', 'completado')
                    ->orderBy('created_at', 'asc')
                    ->get();
                    
                $gastosPendientes = Gasto::where('user_id', $userId)
                    ->whereNull('corte_id')
                    ->orderBy('created_at', 'asc')
                    ->get();
                    
                $totalCobrado = $pagosPendientes->sum('total');
                $totalGastado = $gastosPendientes->sum('monto');
                
                if ($pagosPendientes->isEmpty() && $gastosPendientes->isEmpty()) {
                    throw new \Exception('No hay cobros ni gastos pendientes para generar un corte.');
                }
                
                // 2. Determinar fecha de inicio
                $ultimoCorte = Corte::where('user_id', $userId)->orderBy('id', 'desc')->first();
                if ($ultimoCorte) {
                    $fechaInicio = $ultimoCorte->fecha_fin;
                } else {
                    $fechas = collect();
                    if ($pagosPendientes->isNotEmpty()) $fechas->push($pagosPendientes->first()->created_at);
                    if ($gastosPendientes->isNotEmpty()) $fechas->push($gastosPendientes->first()->created_at);
                    $fechaInicio = $fechas->min() ?: now();
                }
                
                $fechaFin = now();
                
                // 3. Crear el registro de Corte
                $corte = Corte::create([
                    'user_id' => $userId,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'total_cobrado' => $totalCobrado,
                    'total_gastado' => $totalGastado
                ]);
                
                // 4. Asignar el corte_id a los pagos y gastos correspondientes
                Pago::whereIn('id', $pagosPendientes->pluck('id'))->update(['corte_id' => $corte->id]);
                Gasto::whereIn('id', $gastosPendientes->pluck('id'))->update(['corte_id' => $corte->id]);
                
                return $corte;
            });

            return redirect()->route('contabilidad.corte_caja')
                ->with('success', 'Corte de caja #' . $corte->id . ' generado correctamente.')
                ->with('open_corte_pdf_url', route('contabilidad.corte_caja.pdf', $corte->id));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Generar e Imprimir PDF del Corte de Caja
     */
    public function pdfCorteCaja(Corte $corte)
    {
        // El cajero solo puede ver sus propios cortes, el administrador todos
        if (!Auth::user()->hasRole('administrador') && $corte->user_id !== Auth::id()) {
            abort(403, 'No tiene permiso para ver este corte de caja.');
        }

        $corte->load(['cajero', 'pagos.alumno', 'gastos']);

        $pdf = Pdf::loadView('contabilidad.corte_caja_pdf', compact('corte'));
        
        return $pdf->stream('Corte_Caja_' . $corte->id . '_' . str_replace(' ', '_', $corte->cajero->name) . '.pdf');
    }
}
