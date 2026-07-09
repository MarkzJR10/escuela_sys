<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Caja #{{ str_pad($corte->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 15px; }
        .title { font-size: 20px; font-weight: bold; margin: 5px 0; text-transform: uppercase; }
        .subtitle { font-size: 13px; color: #555; }
        
        .info-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .info-table td { padding: 4px 6px; }
        
        .summary-card { width: 100%; border: 1px solid #ccc; border-radius: 4px; background-color: #fafafa; padding: 10px; margin-bottom: 20px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 4px; font-size: 14px; }
        .summary-val { text-align: right; font-weight: bold; }
        
        .section-title { font-size: 14px; font-weight: bold; text-transform: uppercase; margin-top: 15px; margin-bottom: 6px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .data-table th, .data-table td { border: 1px solid #ddd; padding: 6px 8px; font-size: 11px; }
        .data-table th { background-color: #f5f5f5; font-weight: bold; text-align: center; text-transform: uppercase; }
        .data-table td { text-align: center; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        
        .signatures-table { width: 100%; margin-top: 60px; border-collapse: collapse; page-break-inside: avoid; }
        .signature-cell { width: 50%; text-align: center; vertical-align: top; }
        .signature-line { width: 220px; border-top: 1px solid #333; margin: 40px auto 5px auto; }
        
        .footer { position: fixed; bottom: 15px; width: 100%; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Colegio {{ config('app.name') }}</div>
        <div class="subtitle">Reporte Oficial de Corte de Caja</div>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;"><strong>Folio de Corte:</strong> #{{ str_pad($corte->id, 5, '0', STR_PAD_LEFT) }}</td>
            <td style="width: 50%;"><strong>Cajero:</strong> {{ $corte->cajero->name }}</td>
        </tr>
        <tr>
            <td><strong>Fecha Inicio:</strong> {{ $corte->fecha_inicio ? $corte->fecha_inicio->format('d/m/Y H:i:s') : 'N/A' }}</td>
            <td><strong>Fecha Fin:</strong> {{ $corte->fecha_fin ? $corte->fecha_fin->format('d/m/Y H:i:s') : 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Fecha de Impresión:</strong> {{ now()->format('d/m/Y H:i:s') }}</td>
        </tr>
    </table>

    <div class="summary-card">
        <h4 style="margin: 0 0 8px 0; text-transform: uppercase; font-size: 14px; border-bottom: 1px solid #ddd; padding-bottom: 4px;">Resumen Financiero</h4>
        <table class="summary-table">
            <tr>
                <td>Total Ingresos (Cobros en Caja)</td>
                <td class="summary-val" style="color: green;">+ ${{ number_format($corte->total_cobrado, 2) }}</td>
            </tr>
            <tr>
                <td>Total Egresos (Gastos/Retiros)</td>
                <td class="summary-val" style="color: red;">- ${{ number_format($corte->total_gastado, 2) }}</td>
            </tr>
            <tr style="border-top: 1px solid #ccc; font-weight: bold; font-size: 16px;">
                <td style="padding-top: 8px;">Efectivo Neto Entregado</td>
                <td class="summary-val" style="padding-top: 8px; font-size: 16px;">${{ number_format($corte->total_cobrado - $corte->total_gastado, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Tickets / Cobros Detalle -->
    <div class="section-title">Detalle de Cobros / Tickets</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 15%;">Ticket</th>
                <th style="width: 20%;">Fecha/Hora</th>
                <th style="width: 45%;" class="text-left">Alumno</th>
                <th style="width: 20%;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($corte->pagos as $pago)
            <tr>
                <td class="font-weight-bold">{{ $pago->referencia_ticket }}</td>
                <td>{{ $pago->fecha_pago ? $pago->fecha_pago->format('d/m/Y H:i') : $pago->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-left">{{ $pago->alumno->apellido_paterno }} {{ $pago->alumno->apellido_materno }} {{ $pago->alumno->nombre }}</td>
                <td class="text-right font-weight-bold">${{ number_format($pago->total, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="color: #777; font-style: italic; padding: 10px;">No se registraron cobros en este periodo de caja.</td>
            </tr>
            @endforelse
        </tbody>
        @if($corte->pagos->isNotEmpty())
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Total Cobrado:</th>
                <th class="text-right" style="font-size: 12px;">${{ number_format($corte->total_cobrado, 2) }}</th>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Gastos Detalle -->
    <div class="section-title">Detalle de Gastos / Retiros</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;" class="text-left">Concepto</th>
                <th style="width: 20%;">Fecha</th>
                <th style="width: 35%;" class="text-left">Observaciones</th>
                <th style="width: 15%;" class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($corte->gastos as $gasto)
            <tr>
                <td class="text-left font-weight-bold">{{ $gasto->concepto }}</td>
                <td>{{ $gasto->created_at ? $gasto->created_at->format('d/m/Y H:i') : \Carbon\Carbon::parse($gasto->fecha)->format('d/m/Y') }}</td>
                <td class="text-left" style="color: #666;">{{ $gasto->observaciones ?: '-' }}</td>
                <td class="text-right text-danger font-weight-bold">${{ number_format($gasto->monto, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="color: #777; font-style: italic; padding: 10px;">No se registraron gastos o retiros en este periodo de caja.</td>
            </tr>
            @endforelse
        </tbody>
        @if($corte->gastos->isNotEmpty())
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Total Gastado:</th>
                <th class="text-right" style="font-size: 12px; color: red;">${{ number_format($corte->total_gastado, 2) }}</th>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Firmas -->
    <table class="signatures-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <strong>{{ $corte->cajero->name }}</strong><br>
                <span style="font-size: 11px; color: #555;">Firma de Cajero(a)</span>
            </td>
            <td class="signature-cell">
                <div class="signature-line"></div>
                <strong>Administrador Escolar</strong><br>
                <span style="font-size: 11px; color: #555;">Firma de Aprobación</span>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento oficial generado por el sistema de control escolar Colegio {{ config('app.name') }}.
    </div>
</body>
</html>
