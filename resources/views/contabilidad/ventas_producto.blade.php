@extends('adminlte::page')

@section('title', 'Ventas por Producto')

@section('content_header')
    <h1>Ventas por Producto / Concepto</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <form action="{{ route('contabilidad.ventas_producto') }}" method="GET" class="form-inline">
                    <label for="fecha_inicio" class="mr-2">Desde:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control mr-3" value="{{ $fechaInicio }}">

                    <label for="fecha_fin" class="mr-2">Hasta:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control mr-3" value="{{ $fechaFin }}">

                    <button type="submit" class="btn btn-dark"><i class="fas fa-search"></i> Consultar</button>
                    <button type="button" class="btn btn-secondary ml-auto" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                </form>
            </div>
            
            <div class="card-body">
                @if($detalles->count() > 0)
                    <table id="ventas-producto-table" class="table table-bordered table-striped text-sm">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Id Venta</th>
                                <th>Concepto / Producto</th>
                                <th>Cliente</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th class="text-right">Total Vendido</th>
                                <th class="text-center">Cobro</th>
                                <th class="text-center">Ticket</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $granTotal = 0; @endphp
                            @foreach($detalles as $detalle)
                            @php
                                $granTotal += $detalle->monto_pagado;
                                $alumno = optional($detalle->adeudo)->alumno ?? optional($detalle->pago)->alumno;
                                $padre = optional($alumno)->padre;
                            @endphp
                            <tr>
                                <td><span class="badge badge-info">#{{ str_pad($detalle->pago_id ?? $detalle->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td><strong>{{ optional($detalle->adeudo)->concepto ?? 'Producto/Concepto' }}</strong></td>
                                <td>
                                    @if($padre)
                                        <i class="fas fa-user-tie text-primary mr-1"></i><strong>{{ $padre->nombre }} {{ $padre->apellido_paterno }}</strong>
                                        @if($alumno)
                                            <br><small class="text-muted"><i class="fas fa-user-graduate text-success mr-1"></i>Alumno: {{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</small>
                                        @endif
                                    @elseif($alumno)
                                        <i class="fas fa-user-graduate text-success mr-1"></i><strong>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</strong>
                                    @else
                                        <span class="text-muted">General</span>
                                    @endif
                                </td>
                                <td>{{ optional(optional($detalle->pago)->cajero)->name ?? 'Sistema' }}</td>
                                <td>{{ optional($detalle->pago)->fecha_pago ? optional($detalle->pago)->fecha_pago->format('d/m/Y h:i A') : $detalle->created_at->format('d/m/Y h:i A') }}</td>
                                <td class="text-right font-weight-bold text-success">${{ number_format($detalle->monto_pagado, 2) }}</td>
                                <td class="text-center"><span class="badge badge-secondary">{{ ucfirst(optional($detalle->pago)->metodo_pago ?? 'efectivo') }}</span></td>
                                <td class="text-center">
                                    @if(optional($detalle->pago)->id)
                                        <a href="{{ route('pagos.ticket', $detalle->pago->id) }}" target="_blank" class="btn btn-xs btn-info" title="Ver Ticket">
                                            <i class="fas fa-receipt"></i> #{{ $detalle->pago->referencia_ticket ?? str_pad($detalle->pago->id, 6, '0', STR_PAD_LEFT) }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Total General:</th>
                                <th class="text-right h5 text-danger">${{ number_format($granTotal, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="alert alert-info">No se registraron ventas de productos o conceptos en el periodo seleccionado.</div>
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

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#ventas-producto-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 25,
            "responsive": true,
            "order": [[0, "desc"]],
            "columnDefs": [
                { "orderable": false, "targets": [6, 7] }
            ]
        });
    });
</script>
@stop
