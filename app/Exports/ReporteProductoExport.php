<?php

namespace App\Exports;

use App\Models\Producto;
use App\Models\PagoDetalle;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReporteProductoExport implements FromCollection, WithHeadings, WithMapping
{
    protected $productoId;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($productoId = null, $fechaInicio = null, $fechaFin = null)
    {
        $this->productoId = $productoId;
        $this->fechaInicio = $fechaInicio ?: now()->startOfMonth()->toDateString();
        $this->fechaFin = $fechaFin ?: now()->toDateString();
    }

    public function collection()
    {
        $productos = Producto::all();
        $selectedProducto = $this->productoId ? Producto::find($this->productoId) : null;

        $detalles = PagoDetalle::with(['pago.cajero', 'adeudo'])
            ->whereHas('pago', function($q) {
                $q->whereBetween(DB::raw('DATE(fecha_pago)'), [$this->fechaInicio, $this->fechaFin])
                  ->where('status', 'completado');
            })
            ->orderBy('id', 'desc')
            ->get();

        $ventas = collect();

        foreach ($detalles as $detalle) {
            $concepto = optional($detalle->adeudo)->concepto;
            if (!$concepto) continue;

            $nombreProducto = $concepto;
            $cantidad = 1;

            if (preg_match('/^(.*)\s+\(x(\d+)\)$/', $concepto, $matches)) {
                $nombreProducto = trim($matches[1]);
                $cantidad = (int) $matches[2];
            }

            if ($selectedProducto) {
                if (mb_strtolower(trim($nombreProducto)) !== mb_strtolower(trim($selectedProducto->nombre))) {
                    continue;
                }
            } else {
                $esTipoVenta = optional($detalle->adeudo)->tipo === 'venta';
                $coincideConCatalogo = $productos->contains(function($p) use ($nombreProducto) {
                    return mb_strtolower(trim($p->nombre)) === mb_strtolower(trim($nombreProducto));
                });

                if (!$esTipoVenta && !$coincideConCatalogo) {
                    continue;
                }
            }

            $ventas->push((object)[
                'descripcion' => $concepto,
                'cantidad' => $cantidad,
                'fecha' => optional($detalle->pago)->fecha_pago ? optional($detalle->pago)->fecha_pago->format('d/m/Y h:i A') : $detalle->created_at->format('d/m/Y h:i A'),
                'cajero' => optional(optional($detalle->pago)->cajero)->name ?? 'Sistema',
                'ticket' => optional($detalle->pago)->referencia_ticket ?? (optional($detalle->pago)->id ? str_pad($detalle->pago->id, 6, '0', STR_PAD_LEFT) : 'N/A'),
                'monto_pagado' => $detalle->monto_pagado,
            ]);
        }

        return $ventas;
    }

    public function headings(): array
    {
        return [
            'Descripción',
            'Cantidad Vendida',
            'Fecha',
            'Usuario que Vendió',
            'Ticket Asociado',
            'Monto Pagado ($)'
        ];
    }

    public function map($venta): array
    {
        return [
            $venta->descripcion,
            $venta->cantidad,
            $venta->fecha,
            $venta->cajero,
            '#' . $venta->ticket,
            number_format($venta->monto_pagado, 2)
        ];
    }
}
