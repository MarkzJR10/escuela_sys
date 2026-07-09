<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\Adeudo;
use App\Models\Gasto;
use App\Models\Discrepancia;
use App\Models\User;
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
        
        $pagos = Pago::with(['alumno', 'cajero', 'detalles'])
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
}
