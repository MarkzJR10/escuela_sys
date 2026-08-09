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

    <!-- PLANTILLA TÉRMICA ESPECIAL PARA IMPRESORA POS (Oculta en pantalla, visible SOLO al imprimir, basada en Recibo.vue) -->
    <div class="thermal-print-only">
        <div class="ticket-container">
            <!-- Header / Logo -->
            <div class="t-center t-mb-3">
                <h1 class="t-title">SISTEMA ESCOLAR</h1>
                <p class="t-subtitle">COMPROBANTE OFICIAL DE PAGO</p>
                <p class="t-folio">Folio: {{ $pago->referencia_ticket }}</p>
            </div>

            <!-- Divider -->
            <div class="t-divider"></div>

            <!-- Ticket Details -->
            <div class="t-details">
                <div class="t-flex-between">
                    <span class="t-bold">FECHA:</span>
                    <span>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</span>
                </div>
                <div class="t-flex-between">
                    <span class="t-bold">CAJERO:</span>
                    <span class="t-truncate">{{ $pago->cajero->name ?? 'Sistema' }}</span>
                </div>
            </div>

            <!-- Divider -->
            <div class="t-divider"></div>

            <!-- Client Info -->
            <div class="t-client">
                @if($pago->alumno && $pago->alumno->padre)
                    <div class="t-bold t-uppercase t-mb-05">TUTOR:</div>
                    <div class="t-pl-1 t-mb-1">
                        <p class="t-bold t-truncate">{{ $pago->alumno->padre->nombre }} {{ $pago->alumno->padre->apellido_paterno }}</p>
                    </div>
                    <div class="t-bold t-uppercase t-mb-05">ALUMNO:</div>
                    <div class="t-pl-1">
                        <p class="t-bold t-truncate">{{ $pago->alumno->nombre }} {{ $pago->alumno->apellido_paterno }}</p>
                        <p>Matrícula: {{ $pago->alumno->matricula }}</p>
                    </div>
                @else
                    <div class="t-bold t-uppercase t-mb-05">ALUMNO:</div>
                    <div class="t-pl-1">
                        <p class="t-bold t-truncate">{{ $pago->alumno->nombre ?? 'Cliente' }} {{ $pago->alumno->apellido_paterno ?? '' }}</p>
                        @if($pago->alumno)
                            <p>Matrícula: {{ $pago->alumno->matricula }}</p>
                            <p>Grado: {{ $pago->alumno->gradoGrupo->grado ?? '' }} {{ $pago->alumno->gradoGrupo->grupo ?? '' }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Divider -->
            <div class="t-divider"></div>

            <!-- Sale Details / Items -->
            <div class="t-items">
                <div class="t-bold t-uppercase t-mb-1">DESGLOSE DE PAGOS:</div>
                <div class="t-item-list">
                    @php $tOriginal = 0; $tDesc = 0; @endphp
                    @foreach($pago->detalles as $detalle)
                        <div class="t-item-row">
                            <div class="t-flex-between t-bold t-leading-tight">
                                <span class="t-item-name">{{ $detalle->adeudo->tipo == 'colegiatura' ? 'Colegiatura ' . $detalle->adeudo->mes_nombre . ' ' . $detalle->adeudo->anio : $detalle->adeudo->concepto }}</span>
                                <span>${{ number_format($detalle->monto_pagado, 2) }}</span>
                            </div>
                            @if($detalle->notas)
                                <p class="t-item-note">Nota: {{ $detalle->notas }}</p>
                            @endif
                        </div>
                        @php $tOriginal += $detalle->monto_adeudo; $tDesc += $detalle->descuento; @endphp
                    @endforeach
                </div>
            </div>

            <!-- Divider -->
            <div class="t-divider"></div>

            <!-- Summary / Totals -->
            <div class="t-summary">
                @if($tDesc > 0)
                    <div class="t-flex-between">
                        <span>Subtotal:</span>
                        <span>${{ number_format($tOriginal, 2) }}</span>
                    </div>
                    <div class="t-flex-between">
                        <span>Descuento:</span>
                        <span>-${{ number_format($tDesc, 2) }}</span>
                    </div>
                @endif
                <div class="t-flex-between t-total-row">
                    <span>TOTAL PAGADO:</span>
                    <span>${{ number_format($pago->total, 2) }}</span>
                </div>
                <div class="t-flex-between">
                    <span class="t-bold">MÉTODO:</span>
                    <span class="t-bold t-uppercase">{{ $pago->metodo_pago ?? 'efectivo' }}</span>
                </div>
            </div>

            <!-- Divider -->
            <div class="t-divider"></div>

            <!-- Signatures stacked (POS ticket style) -->
            <div class="t-signatures">
                <div class="t-sign-line">
                    <p class="t-bold">FIRMA TUTOR / ALUMNO</p>
                    <p class="t-subtext">(Conformidad)</p>
                </div>
            </div>

            <!-- Footer Message -->
            <div class="t-footer">
                <p class="t-bold">¡GRACIAS POR TU PREFERENCIA!</p>
                <p>Conserva este comprobante para aclaraciones.</p>
                <p class="t-copy">SISTEMA ESCOLAR © {{ date('Y') }}</p>
            </div>
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

        @page { 
            size: 58mm auto; /* Ancho físico del papel de la ticketera */
            margin: 0; 
        }

        html, body, .wrapper, .content-wrapper, .container-fluid, .content {
            background-color: #ffffff !important; 
            color: #000000 !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            box-shadow: none !important;
            border: none !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Mostrar ÚNICAMENTE la plantilla térmica especial alineada exactamente a 42mm con margen de seguridad 1.5mm */
        .thermal-print-only {
            display: block !important;
            width: 42mm !important;
            margin-left: 1.5mm !important;
            margin-right: auto !important;
            padding: 4px 2px !important;
            font-family: system-ui, -apple-system, sans-serif !important;
            font-size: 10px !important;
            color: #000000 !important;
        }

        .ticket-container {
            width: 42mm !important;
            color: #000000 !important;
        }

        /* Pure black text without dithering */
        .thermal-print-only * {
            color: #000000 !important;
            text-shadow: none !important;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .t-center { text-align: center !important; }
        .t-bold { font-weight: bold !important; }
        .t-uppercase { text-transform: uppercase !important; }
        .t-mb-3 { margin-bottom: 8px !important; }
        .t-mb-1 { margin-bottom: 4px !important; }
        .t-mb-05 { margin-bottom: 2px !important; }
        .t-pl-1 { padding-left: 4px !important; }
        .t-leading-tight { line-height: 1.15 !important; }

        .t-title {
            font-size: 12px !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            margin: 0 0 2px 0 !important;
        }

        .t-subtitle {
            font-size: 10px !important;
            font-weight: bold !important;
            text-transform: uppercase !important;
            margin: 0 0 2px 0 !important;
        }

        .t-folio {
            font-size: 9px !important;
            font-family: monospace !important;
            margin: 2px 0 0 0 !important;
        }

        .t-divider {
            border-bottom: 1px dashed #000000 !important;
            margin: 6px 0 !important;
        }

        .t-details, .t-client, .t-summary {
            font-size: 10px !important;
        }

        .t-flex-between {
            display: flex !important;
            justify-content: space-between !important;
        }

        .t-truncate {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-width: 65% !important;
        }

        .t-items {
            margin-bottom: 6px !important;
        }

        .t-item-list {
            padding-left: 2px !important;
        }

        .t-item-row {
            border-bottom: 1px solid #f0f0f0 !important;
            padding-bottom: 3px !important;
            margin-bottom: 3px !important;
        }

        .t-item-name {
            max-width: 70% !important;
            word-break: break-words !important;
        }

        .t-item-note {
            font-size: 8.5px !important;
            font-style: italic !important;
            color: #000000 !important;
            margin: 2px 0 0 0 !important;
            word-break: break-words !important;
        }

        .t-total-row {
            font-size: 11px !important;
            font-weight: bold !important;
        }

        .t-signatures {
            margin-top: 12px !important;
            text-align: center !important;
            font-size: 10px !important;
        }

        .t-sign-line {
            width: 90px !important;
            margin: 0 auto !important;
            padding-top: 8px !important;
            border-top: 1px solid #000000 !important;
        }

        .t-subtext {
            font-size: 9px !important;
            margin: 0 !important;
        }

        .t-footer {
            margin-top: 14px !important;
            text-align: center !important;
            font-size: 9px !important;
            line-height: 1.15 !important;
            font-weight: 500 !important;
        }

        .t-footer p {
            margin: 2px 0 !important;
        }

        .t-copy {
            margin-top: 5px !important;
            font-weight: bold !important;
        }
    }
</style>
@stop