@extends('adminlte::page')

@section('title', 'Lista de Ventas')

@section('content_header')
    <h1>Auditoría de Ventas / Tickets</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card card-primary">
            <div class="card-header">
                <form action="{{ route('contabilidad.ventas') }}" method="GET" class="form-inline">
                    <label for="fecha" class="mr-2">Fecha de consulta:</label>
                    <input type="date" name="fecha" id="fecha" class="form-control mr-2" value="{{ $fecha }}">
                    <button type="submit" class="btn btn-dark"><i class="fas fa-search"></i> Buscar</button>
                </form>
            </div>
            
            <div class="card-body">
                @if($pagos->count() > 0)
                    <table class="table table-bordered table-striped table-hover data-table text-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Folio Ticket</th>
                                <th>Hora</th>
                                <th>Cajero</th>
                                <th>Alumno</th>
                                <th>Conceptos</th>
                                <th class="text-right">Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagos as $pago)
                            <tr>
                                <td><span class="badge badge-info">#{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</span><br><small>{{ $pago->referencia_ticket }}</small></td>
                                <td>{{ $pago->fecha_pago->format('h:i A') }}</td>
                                <td>{{ $pago->cajero->name ?? 'Sistema' }}</td>
                                <td>{{ $pago->alumno->nombre ?? 'Venta' }} {{ $pago->alumno->apellido_paterno ?? 'Público' }}</td>
                                <td>
                                    <ul class="list-unstyled mb-0 pl-0">
                                        @foreach($pago->detalles as $detalle)
                                            <li>- {{ optional($detalle->adeudo)->tipo == 'colegiatura' ? 'Colegiatura ' . optional($detalle->adeudo)->mes_nombre . ' ' . optional($detalle->adeudo)->anio : (optional($detalle->adeudo)->concepto ?? 'Pago') }} (${{ number_format($detalle->monto_pagado, 2) }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="text-right font-weight-bold text-success">${{ number_format($pago->total, 2) }}</td>
                                <td>
                                    <a href="{{ route('pagos.ticket', $pago->id) }}" target="_blank" class="btn btn-sm btn-primary mb-1" title="Reimprimir Ticket">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <form action="{{ route('contabilidad.ventas.cancelar', $pago->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger mb-1" onclick="return confirm('¿Está seguro de CANCELAR este ticket? Los adeudos volverán a estar pendientes para cobro.')" title="Cancelar Ticket">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">No se encontraron ventas completadas para la fecha seleccionada.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
