@extends('adminlte::page')

@section('title', 'Ticket de Pago ' . $pago->referencia_ticket)

@section('content_header')
    <h1>Comprobante de Pago</h1>
@stop

@section('content')
    <!-- PLANTILLA WEB EN PANTALLA (oculta únicamente al imprimir) -->
    <div class="invoice p-3 mb-3 web-ticket-view" id="printable-ticket">
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

    <!-- PLANTILLA TÉRMICA ESPECIAL (Oculta en pantalla, visible SOLO al imprimir) -->
    <div class="thermal-print-only">
        <div class="t-center t-title">SISTEMA ESCOLAR</div>
        <div class="t-center t-subtitle">COMPROBANTE OFICIAL DE PAGO</div>
        <div class="t-line"></div>
        
        <div class="t-info">
            <div>Ticket #: {{ $pago->referencia_ticket }}</div>
            <div>Fecha: {{ $pago->fecha_pago->format('d/m/Y H:i') }}</div>
            <div>Cajero: {{ $pago->cajero->name ?? 'Sistema' }}</div>
            @if($pago->metodo_pago)
                <div>Método Pago: {{ strtoupper($pago->metodo_pago) }}</div>
            @endif
            <div class="t-line"></div>
            
            @if($pago->alumno && $pago->alumno->padre)
                <div>Tutor: {{ $pago->alumno->padre->nombre }} {{ $pago->alumno->padre->apellido_paterno }}</div>
                <div>Alumno: {{ $pago->alumno->nombre }} {{ $pago->alumno->apellido_paterno }}</div>
                <div>Matrícula: {{ $pago->alumno->matricula }}</div>
            @else
                <div>Alumno: {{ $pago->alumno->nombre ?? 'Cliente' }} {{ $pago->alumno->apellido_paterno ?? '' }}</div>
                @if($pago->alumno)
                    <div>Matrícula: {{ $pago->alumno->matricula }}</div>
                @endif
            @endif
        </div>

        <div class="t-line"></div>

        <table class="t-table">
            <thead>
                <tr>
                    <th class="t-left">CONCEPTO</th>
                    <th class="t-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php $tOriginal = 0; $tDesc = 0; @endphp
                @foreach($pago->detalles as $detalle)
                    <tr>
                        <td class="t-left">
                            {{ $detalle->adeudo->tipo == 'colegiatura' ? 'Colegiatura ' . $detalle->adeudo->mes_nombre . ' ' . $detalle->adeudo->anio : $detalle->adeudo->concepto }}
                            @if($detalle->notas)
                                <br><span class="t-subnote">({{ $detalle->notas }})</span>
                            @endif
                        </td>
                        <td class="t-right t-top">${{ number_format($detalle->monto_pagado, 2) }}</td>
                    </tr>
                    @php $tOriginal += $detalle->monto_adeudo; $tDesc += $detalle->descuento; @endphp
                @endforeach
            </tbody>
        </table>

        <div class="t-line"></div>

        @if($tDesc > 0)
            <div class="t-row">
                <span>Subtotal:</span>
                <span>${{ number_format($tOriginal, 2) }}</span>
            </div>
            <div class="t-row">
                <span>Descuento:</span>
                <span>-${{ number_format($tDesc, 2) }}</span>
            </div>
        @endif

        <div class="t-row t-total">
            <span>TOTAL PAGADO:</span>
            <span>${{ number_format($pago->total, 2) }}</span>
        </div>

        <div class="t-line"></div>
        <div class="t-center t-footer">
            ¡Gracias por su pago!<br>
            Conserve este comprobante.
        </div>
    </div>
@stop

@section('css')
<style>
    /* Ocultar plantilla térmica en la vista web del navegador */
    .thermal-print-only {
        display: none !important;
    }

    @media print {
        /* Ocultar plantilla web e interfaz del sistema al imprimir */
        nav, .main-header, .main-sidebar, .content-header, .main-footer, .no-print, .web-ticket-view {
            display: none !important;
        }

        /* Mostrar ÚNICAMENTE la plantilla térmica especial */
        .thermal-print-only {
            display: block !important;
            width: 58mm !important;
            max-width: 58mm !important;
            margin: 0 auto !important;
            padding: 2px !important;
            box-sizing: border-box !important;
        }

        html, body, .wrapper, .content-wrapper, .container-fluid, .content {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            box-shadow: none !important;
            border: none !important;
        }

        @page {
            size: 58mm auto;
            margin: 0mm;
        }

        .thermal-print-only {
            font-family: 'Courier New', Courier, monospace, sans-serif !important;
            font-size: 8.5pt !important;
            line-height: 1.2 !important;
            color: #000000 !important;
        }

        .t-center { text-align: center !important; }
        .t-left { text-align: left !important; }
        .t-right { text-align: right !important; }
        .t-top { vertical-align: top !important; }
        
        .t-title {
            font-size: 10.5pt !important;
            font-weight: bold !important;
        }

        .t-subtitle {
            font-size: 8pt !important;
        }

        .t-line {
            border-top: 1px dashed #000 !important;
            margin: 4px 0 !important;
        }

        .t-info {
            font-size: 8pt !important;
        }

        .t-table {
            width: 100% !important;
            margin: 3px 0 !important;
        }

        .t-table th, .t-table td {
            font-size: 8pt !important;
            padding: 1px 0 !important;
        }

        .t-subnote {
            font-size: 7.5pt !important;
        }

        .t-row {
            display: flex !important;
            justify-content: space-between !important;
            font-size: 8.5pt !important;
        }

        .t-total {
            font-size: 10.5pt !important;
            font-weight: bold !important;
            margin-top: 3px !important;
        }

        .t-footer {
            font-size: 7.5pt !important;
            margin-top: 3px !important;
        }
    }
</style>
@stop