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
                    @if($pago->alumno && $pago->alumno->padre)
                        <strong>Tutor: {{ $pago->alumno->padre->nombre }} {{ $pago->alumno->padre->apellido_paterno }}</strong><br>
                        <small class="text-muted">Alumno: {{ $pago->alumno->nombre }} {{ $pago->alumno->apellido_paterno }} (Matrícula: {{ $pago->alumno->matricula }})</small><br>
                    @else
                        <strong>{{ $pago->alumno->nombre ?? 'Cliente' }} {{ $pago->alumno->apellido_paterno ?? '' }} {{ $pago->alumno->apellido_materno ?? '' }}</strong><br>
                        @if($pago->alumno)
                            Matrícula: <code>{{ $pago->alumno->matricula }}</code><br>
                            Grado: {{ $pago->alumno->gradoGrupo->grado ?? '' }} {{ $pago->alumno->gradoGrupo->grupo ?? '' }}
                        @endif
                    @endif
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
                            <th class="text-right col-original">Monto Original</th>
                            <th class="text-right col-descuento">Descuento Aplicado</th>
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
                                <td class="text-right col-original">${{ number_format($detalle->monto_adeudo, 2) }}</td>
                                <td class="text-right text-danger col-descuento">-${{ number_format($detalle->descuento, 2) }}</td>
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
                        <tr style="font-size: 1.4rem;" class="tr-total-pagado">
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
        /* Ocultar interfaz del sistema y elementos no imprimibles */
        nav, .main-header, .main-sidebar, .content-header, .main-footer, .no-print, #printable-ticket .no-print {
            display: none !important;
        }

        body, .content-wrapper, .wrapper, .container-fluid {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        @page {
            size: 58mm 297mm;
            margin: 0mm;
        }

        #printable-ticket {
            width: 58mm !important;
            max-width: 58mm !important;
            margin: 0 auto !important;
            padding: 3mm 2mm !important;
            font-family: 'Courier New', Courier, monospace, sans-serif !important;
            font-size: 8.5pt !important;
            line-height: 1.2 !important;
            color: #000 !important;
            background: #fff !important;
            border: none !important;
            box-shadow: none !important;
            box-sizing: border-box !important;
        }

        /* Transformar filas y columnas a 100% para tira continua de 58mm */
        #printable-ticket .row:not(.no-print), 
        #printable-ticket .col-12:not(.no-print), 
        #printable-ticket .col-sm-4, 
        #printable-ticket .col-6 {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        #printable-ticket h4 {
            font-size: 10pt !important;
            font-weight: bold !important;
            text-align: center !important;
            margin-bottom: 4px !important;
        }

        #printable-ticket h4 small {
            display: block !important;
            float: none !important;
            font-size: 8pt !important;
            margin-top: 3px;
        }

        .invoice-info {
            margin-top: 6px !important;
            margin-bottom: 6px !important;
            font-size: 8pt !important;
            border-top: 1px dashed #000 !important;
            border-bottom: 1px dashed #000 !important;
            padding: 4px 0 !important;
        }

        .invoice-col {
            margin-bottom: 5px !important;
        }

        /* Ocultar columnas intermedias no esenciales para ajustar a 58mm al imprimir */
        .col-original, .col-descuento {
            display: none !important;
        }

        .table {
            width: 100% !important;
            margin: 5px 0 !important;
            font-size: 8pt !important;
        }

        .table th, .table td {
            padding: 2px 0 !important;
            border: none !important;
            background: transparent !important;
        }

        .table thead tr {
            border-bottom: 1px dashed #000 !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        /* Ajuste específico para que Total Pagado quede alineado en 58mm */
        .tr-total-pagado {
            font-size: 9.5pt !important;
        }

        .tr-total-pagado th, .tr-total-pagado td {
            font-size: 9.5pt !important;
            font-weight: bold !important;
            white-space: nowrap !important;
        }

        .lead {
            font-size: 8pt !important;
            font-weight: bold !important;
            margin-top: 6px !important;
            margin-bottom: 3px !important;
            border-top: 1px dashed #000 !important;
            padding-top: 3px !important;
        }

        .well {
            font-size: 7.5pt !important;
            padding: 0 !important;
            margin-bottom: 6px !important;
        }

        .badge-success {
            background-color: transparent !important;
            color: #000 !important;
            border: none !important;
            padding: 0 !important;
        }

        .text-muted, .text-info, .text-danger {
            color: #000 !important;
        }
    }
</style>
@stop