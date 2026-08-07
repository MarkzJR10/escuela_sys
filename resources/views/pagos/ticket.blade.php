@extends('adminlte::page')

@section('title', 'Ticket de Pago ' . $pago->referencia_ticket)

@section('content_header')
    <h1 class="no-print">Comprobante de Pago</h1>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card card-outline card-primary shadow-sm" id="printable-ticket">
                <div class="card-body p-3">
                    <!-- Encabezado del Ticket -->
                    <div class="text-center ticket-header mb-2">
                        <h5 class="font-weight-bold mb-1"><i class="fas fa-school"></i> Sistema Escolar</h5>
                        <div class="small">Ticket #<strong>{{ $pago->referencia_ticket }}</strong></div>
                        <div class="small text-muted ticket-fecha">Fecha: {{ $pago->fecha_pago->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="ticket-divider"></div>

                    <!-- Datos del Alumno y Cajero -->
                    <div class="ticket-info small my-2">
                        <div><strong>Cajero:</strong> {{ $pago->cajero->name }}</div>
                        <div><strong>Alumno:</strong> {{ $pago->alumno->nombre }} {{ $pago->alumno->apellido_paterno }} {{ $pago->alumno->apellido_materno }}</div>
                        <div><strong>Matrícula:</strong> {{ $pago->alumno->matricula }}</div>
                        <div><strong>Grado:</strong> {{ $pago->alumno->gradoGrupo->grado }} {{ $pago->alumno->gradoGrupo->grupo }}</div>
                        @if($pago->metodo_pago)
                            <div><strong>Método Pago:</strong> {{ ucfirst($pago->metodo_pago) }}</div>
                        @endif
                    </div>

                    <div class="ticket-divider"></div>

                    <!-- Tabla de Conceptos -->
                    <table class="table table-borderless table-sm ticket-table my-2">
                        <thead>
                            <tr class="border-bottom">
                                <th>Concepto</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalDescuentos = 0; $totalOriginal = 0; @endphp
                            @foreach($pago->detalles as $detalle)
                                <tr>
                                    <td>
                                        {{ $detalle->adeudo->tipo == 'colegiatura' ? 'Colegiatura ' . $detalle->adeudo->mes_nombre . ' ' . $detalle->adeudo->anio : $detalle->adeudo->concepto }}
                                        @if($detalle->descuento > 0)
                                            <br><small class="text-danger">(Desc: -${{ number_format($detalle->descuento, 2) }})</small>
                                        @endif
                                        @if($detalle->notas)
                                            <br><small class="text-muted"><em>{{ $detalle->notas }}</em></small>
                                        @endif
                                    </td>
                                    <td class="text-right font-weight-bold align-top">
                                        ${{ number_format($detalle->monto_pagado, 2) }}
                                    </td>
                                </tr>
                                @php 
                                    $totalOriginal += $detalle->monto_adeudo; 
                                    $totalDescuentos += $detalle->descuento; 
                                @endphp
                            @endforeach
                        </tbody>
                    </table>

                    <div class="ticket-divider"></div>

                    <!-- Totales -->
                    <div class="ticket-totals small my-2">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <span>${{ number_format($totalOriginal, 2) }}</span>
                        </div>
                        @if($totalDescuentos > 0)
                            <div class="d-flex justify-content-between text-danger">
                                <span>Descuentos:</span>
                                <span>-${{ number_format($totalDescuentos, 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between font-weight-bold mt-1" style="font-size: 1.1em;">
                            <span>Total Pagado:</span>
                            <span>${{ number_format($pago->total, 2) }}</span>
                        </div>
                    </div>

                    <div class="ticket-divider"></div>

                    <!-- Mensaje Final -->
                    <div class="text-center small ticket-footer mt-2">
                        <p class="mb-0 font-weight-bold">¡Gracias por su pago!</p>
                        <p class="text-muted mb-0" style="font-size: 0.85em;">Comprobante oficial de pago.</p>
                    </div>
                </div>
            </div>

            <!-- Botones (No imprimibles) -->
            <div class="row no-print mb-4">
                <div class="col-12">
                    <button type="button" onclick="window.print();" class="btn btn-success btn-block mb-2">
                        <i class="fas fa-print"></i> Imprimir Ticket (Impresora Térmica 35mm)
                    </button>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('pagos.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Cobro
                        </a>
                        <a href="{{ route('cartera.index') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-search"></i> Buscar Otro Alumno
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .ticket-divider {
        border-top: 1px dashed #999;
        margin: 8px 0;
    }

    @media print {
        /* Ocultar interfaz del sistema */
        nav, .main-header, .main-sidebar, .content-header, .main-footer, .no-print, .card-header {
            display: none !important;
        }

        body, .content-wrapper, .wrapper, .container-fluid, .row, .col-md-6, .col-lg-5 {
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-shadow: none !important;
            border: none !important;
        }

        @page {
            size: 35mm auto;
            margin: 0mm;
        }

        #printable-ticket {
            width: 35mm !important;
            max-width: 35mm !important;
            margin: 0 auto !important;
            padding: 1mm !important;
            font-family: 'Courier New', Courier, monospace, sans-serif !important;
            font-size: 7.5pt !important;
            line-height: 1.15 !important;
            color: #000 !important;
            background: #fff !important;
            box-shadow: none !important;
            border: none !important;
        }

        #printable-ticket .card-body {
            padding: 1mm !important;
        }

        #printable-ticket h5 {
            font-size: 8.5pt !important;
            margin-bottom: 2px !important;
        }

        #printable-ticket .small, #printable-ticket div, #printable-ticket p {
            font-size: 7pt !important;
        }

        .ticket-divider {
            border-top: 1px dashed #000 !important;
            margin: 3px 0 !important;
        }

        .ticket-table {
            width: 100% !important;
            margin: 3px 0 !important;
        }

        .ticket-table th, .ticket-table td {
            padding: 1px 0 !important;
            font-size: 6.5pt !important;
            border: none !important;
        }

        .ticket-table thead tr {
            border-bottom: 1px dashed #000 !important;
        }

        .ticket-totals {
            font-size: 7pt !important;
        }

        .ticket-footer {
            font-size: 6pt !important;
        }

        .text-muted {
            color: #000 !important;
        }
    }
</style>
@stop
