@extends('adminlte::page')

@section('title', 'Contabilidad - Reporte de Ventas')

@section('content_header')
    <h1>Auditoría de Ventas y Cobranza</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header bg-dark">
            <h3 class="card-title">Historial de Tickets y Descuentos</h3>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Fecha</th>
                        <th>Cajero</th>
                        <th>Alumno</th>
                        <th class="text-right">Monto Debido</th>
                        <th class="text-right">Descuentos</th>
                        <th class="text-right">Total Cobrado</th>
                        <th class="text-center">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalDue = 0; 
                        $totalDiscount = 0; 
                        $totalPaid = 0; 
                    @endphp
                    @forelse($pagos as $pago)
                        @php
                            $pagoDue = $pago->detalles->sum('monto_adeudo');
                            $pagoDiscount = $pago->detalles->sum('descuento');
                            
                            $totalDue += $pagoDue;
                            $totalDiscount += $pagoDiscount;
                            $totalPaid += $pago->total;
                        @endphp
                        <tr>
                            <td><code>#{{ $pago->referencia_ticket }}</code></td>
                            <td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                            <td>{{ $pago->cajero->name }}</td>
                            <td>{{ $pago->alumno->nombre }} {{ $pago->alumno->apellidos }}</td>
                            <td class="text-right">${{ number_format($pagoDue, 2) }}</td>
                            <td class="text-right text-danger">-${{ number_format($pagoDiscount, 2) }}</td>
                            <td class="text-right font-weight-bold">${{ number_format($pago->total, 2) }}</td>
                            <td class="text-center">
                                <a href="{{ route('pagos.ticket', $pago->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No se han registrado pagos aún.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($pagos->count() > 0)
                <tfoot>
                    <tr class="bg-light font-weight-bold">
                        <td colspan="4" class="text-right">TOTALES PÁGINA:</td>
                        <td class="text-right">${{ number_format($totalDue, 2) }}</td>
                        <td class="text-right text-danger">-${{ number_format($totalDiscount, 2) }}</td>
                        <td class="text-right text-primary">${{ number_format($totalPaid, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $pagos->appends(request()->query())->links() }}
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Eficiencia de Cobro</span>
                    <span class="info-box-number">
                        {{ $totalDue > 0 ? number_format(($totalPaid / $totalDue) * 100, 1) : 0 }}%
                    </span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ $totalDue > 0 ? ($totalPaid / $totalDue) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
