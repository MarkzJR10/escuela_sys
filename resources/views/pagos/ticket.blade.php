@extends('adminlte::page')

@section('title', 'Ticket de Pago ' . $pago->referencia_ticket)

@section('content_header')
    <h1>Comprobante de Pago</h1>
@stop

@section('content')
    <div class="invoice p-3 mb-3" id="printable-ticket">
        <!-- title row -->
        <div class="row">
            <div class="col-12">
                <h4>
                    <i class="fas fa-school"></i> Sistema Escolar
                    <small class="float-right">Fecha: {{ $pago->fecha_pago->format('d/m/Y H:i') }}</small>
                </h4>
            </div>
            <!-- /.col -->
        </div>
        <!-- info row -->
        <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
                De
                <address>
                    <strong>Departamento de Caja</strong><br>
                    Cajero: {{ $pago->cajero->name }}<br>
                    ID Cajero: #{{ $pago->user_id }}
                </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
                Para
                <address>
                    <strong>{{ $pago->alumno->nombre }} {{ $pago->alumno->apellido_paterno }} {{ $pago->alumno->apellido_materno }}</strong><br>
                    Matrícula: <code>{{ $pago->alumno->matricula }}</code><br>
                    Grado: {{ $pago->alumno->gradoGrupo->grado }} {{ $pago->alumno->gradoGrupo->grupo }}
                </address>
            </div>
            <!-- /.col -->
            <div class="col-sm-4 invoice-col">
                <b>Ticket #{{ $pago->referencia_ticket }}</b><br>
                <br>
                <b>Fecha de Pago:</b> {{ $pago->fecha_pago->format('d/m/Y') }}<br>
                @if($pago->metodo_pago)
                    <b>Método de Pago:</b> {{ ucfirst($pago->metodo_pago) }}<br>
                @endif
                <b>Estado:</b> <span class="badge badge-success">PAGADO</span>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- Table row -->
        <div class="row mt-4">
            <div class="col-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th class="text-right">Monto Original</th>
                            <th class="text-right">Descuento Aplicado</th>
                            <th class="text-right">Importe Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalDescuentos = 0; $totalOriginal = 0; @endphp
                        @foreach($pago->detalles as $detalle)
                            <tr>
                                <td>
                                    {{ $detalle->adeudo->tipo == 'colegiatura' ? 'Colegiatura ' . $detalle->adeudo->mes_nombre . ' ' . $detalle->adeudo->anio : $detalle->adeudo->concepto }}
                                    @if($detalle->notas)
                                        <br><small class="text-info"><em>Nota: {{ $detalle->notas }}</em></small>
                                    @endif
                                </td>
                                <td class="text-right">${{ number_format($detalle->monto_adeudo, 2) }}</td>
                                <td class="text-right text-danger">-${{ number_format($detalle->descuento, 2) }}</td>
                                <td class="text-right font-weight-bold">${{ number_format($detalle->monto_pagado, 2) }}</td>
                            </tr>
                            @php 
                                $totalOriginal += $detalle->monto_adeudo; 
                                $totalDescuentos += $detalle->descuento; 
                            @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

        <div class="row">
            <!-- accepted payments column -->
            <div class="col-6">
                <p class="lead">Notas Generales:</p>
                <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                    Este ticket sirve como comprobante oficial de pago para el ciclo escolar vigente.
                    Consérvelo para cualquier aclaración futura.
                </p>
            </div>
            <!-- /.col -->
            <div class="col-6">
                <div class="table-responsive">
                    <table class="table">
                        <tr>
                            <th style="width:50%">Subtotal:</th>
                            <td class="text-right">${{ number_format($totalOriginal, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-danger">Total Descuentos:</th>
                            <td class="text-right text-danger">-${{ number_format($totalDescuentos, 2) }}</td>
                        </tr>
                        <tr style="font-size: 1.4rem;">
                            <th>Total Pagado:</th>
                            <td class="text-right font-weight-bold">${{ number_format($pago->total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->

        <!-- this row will not appear when printing -->
        <div class="row no-print">
            <div class="col-12">
                <button type="button" onclick="window.print();" class="btn btn-default"><i class="fas fa-print"></i> Imprimir</button>
                <a href="{{ route('pagos.index') }}" class="btn btn-primary float-right" style="margin-right: 5px;">
                    <i class="fas fa-plus"></i> Nuevo Cobro
                </a>
                <a href="{{ route('cartera.index') }}" class="btn btn-info float-right" style="margin-right: 5px;">
                    <i class="fas fa-search"></i> Buscar Otro Alumno
                </a>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@stop
