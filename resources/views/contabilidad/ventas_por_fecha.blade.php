@extends('adminlte::page')

@section('title', 'Ventas por Fecha')

@section('content_header')
    <h1>Reporte de Ventas por Fecha</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-success">
            <div class="card-header">
                <form action="{{ route('contabilidad.ventas_por_fecha') }}" method="GET" class="form-inline">
                    <label for="fecha_inicio" class="mr-2">Desde:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control mr-2" value="{{ $fechaInicio }}">
                    
                    <label for="fecha_fin" class="mr-2">Hasta:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control mr-2" value="{{ $fechaFin }}">

                    <button type="submit" class="btn btn-dark"><i class="fas fa-filter"></i> Generar Reporte</button>
                    <button type="button" class="btn btn-secondary ml-auto" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                </form>
            </div>
            
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Recaudado</span>
                                <span class="info-box-number">${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if($pagos->count() > 0)
                    <table class="table table-bordered table-striped text-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Folio Ticket</th>
                                <th>Cajero</th>
                                <th class="text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagos as $pago)
                            <tr>
                                <td>{{ $pago->fecha_pago->format('d/m/Y h:i A') }}</td>
                                <td>#{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $pago->cajero->name ?? 'Sistema' }}</td>
                                <td class="text-right">${{ number_format($pago->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning">No se registraron ventas en el periodo seleccionado.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, .main-sidebar, .main-header, form { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>
@stop
