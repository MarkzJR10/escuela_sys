@extends('adminlte::page')

@section('title', 'Estado de Cuenta')

@section('content_header')
    <h1>Estado de Cuenta</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="invoice p-3 mb-3">
            <!-- Título del recibo / estado -->
            <div class="row">
                <div class="col-12">
                    <h4>
                        <i class="fas fa-school"></i> {{ config('app.name', 'Colegio') }}
                        <small class="float-right">Fecha de emisión: {{ now()->format('d/m/Y') }}</small>
                    </h4>
                </div>
            </div>
            
            <div class="row invoice-info mt-4">
                <div class="col-sm-6 invoice-col">
                    <strong>Datos del Alumno:</strong>
                    <address>
                        {{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}<br>
                        Matrícula: {{ $alumno->matricula }}<br>
                        Grado y Grupo: {{ $alumno->gradoGrupo->grado ?? '' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"<br>
                    </address>
                </div>
                <div class="col-sm-6 invoice-col">
                    <strong>Padre/Tutor Responsable:</strong>
                    <address>
                        {{ $alumno->padre->nombre ?? 'N/A' }} {{ $alumno->padre->apellido_paterno ?? '' }}<br>
                        Teléfono: {{ $alumno->padre->telefono ?? 'N/A' }}<br>
                    </address>
                </div>
            </div>

            <!-- ADEUDOS -->
            <div class="row mt-4">
                <div class="col-12">
                    <h5 class="text-danger border-bottom pb-2 mb-3"><i class="fas fa-exclamation-triangle"></i> Cargos Pendientes</h5>
                </div>
                <div class="col-12">
                    @php $hasAdeudos = false; @endphp

                    <!-- COLEGIATURAS -->
                    @if($colegiaturas->isNotEmpty())
                        @php $hasAdeudos = true; @endphp
                        <h6 class="text-primary font-weight-bold mt-2"><i class="fas fa-book mr-1"></i> Colegiaturas</h6>
                        <table class="table table-sm table-striped table-hover mb-4">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Periodo</th>
                                    <th>Estado</th>
                                    <th class="text-right" style="width: 150px;">Monto</th>
                                    <th class="text-center" style="width: 120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($colegiaturas as $adeudo)
                                    <tr>
                                        <td>{{ $adeudo->concepto }}</td>
                                        <td>{{ $adeudo->periodo }}</td>
                                        <td>
                                            @if($adeudo->status == 'vencido')
                                                <span class="badge badge-danger">Vencido</span>
                                            @else
                                                <span class="badge badge-warning">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold @if($adeudo->status == 'vencido') text-danger @endif">
                                            ${{ number_format($adeudo->monto_calculado, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('adeudos.cancelar', $adeudo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de cancelar este adeudo de colegiatura?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Cancelar Adeudo">
                                                    <i class="fas fa-ban"></i> Cancelar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- ADEUDOS ESPECIALES -->
                    @if($especiales->isNotEmpty())
                        @php $hasAdeudos = true; @endphp
                        <h6 class="text-info font-weight-bold mt-2"><i class="fas fa-star mr-1"></i> Adeudos Especiales</h6>
                        <table class="table table-sm table-striped table-hover mb-4">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Estado</th>
                                    <th class="text-right" style="width: 150px;">Monto</th>
                                    <th class="text-center" style="width: 120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($especiales as $adeudo)
                                    <tr>
                                        <td>{{ $adeudo->concepto }}</td>
                                        <td>
                                            @if($adeudo->status == 'vencido')
                                                <span class="badge badge-danger">Vencido</span>
                                            @else
                                                <span class="badge badge-warning">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold @if($adeudo->status == 'vencido') text-danger @endif">
                                            ${{ number_format($adeudo->monto_calculado, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('adeudos.cancelar', $adeudo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de cancelar este adeudo especial?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Cancelar Adeudo">
                                                    <i class="fas fa-ban"></i> Cancelar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- VENTAS Y PRODUCTOS -->
                    @if($ventas->isNotEmpty())
                        @php $hasAdeudos = true; @endphp
                        <h6 class="text-success font-weight-bold mt-2"><i class="fas fa-shopping-cart mr-1"></i> Ventas y Consumos (Crédito)</h6>
                        <table class="table table-sm table-striped table-hover mb-4">
                            <thead>
                                <tr>
                                    <th>Concepto</th>
                                    <th>Estado</th>
                                    <th class="text-right" style="width: 150px;">Monto</th>
                                    <th class="text-center" style="width: 120px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventas as $adeudo)
                                    <tr>
                                        <td>{{ $adeudo->concepto }}</td>
                                        <td>
                                            @if($adeudo->status == 'vencido')
                                                <span class="badge badge-danger">Vencido</span>
                                            @else
                                                <span class="badge badge-warning">Pendiente</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold @if($adeudo->status == 'vencido') text-danger @endif">
                                            ${{ number_format($adeudo->monto_calculado, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <form action="{{ route('adeudos.cancelar', $adeudo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de cancelar este adeudo de venta?');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Cancelar Adeudo">
                                                    <i class="fas fa-ban"></i> Cancelar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    @if(!$hasAdeudos)
                        <div class="alert alert-success text-center py-3"><i class="fas fa-check-circle mr-1"></i> El alumno no presenta adeudos en este momento.</div>
                    @else
                        <div class="row">
                            <div class="col-8"></div>
                            <div class="col-4">
                                <table class="table table-sm table-bordered">
                                    <tr class="bg-light">
                                        <th class="text-right">Total General Pendiente:</th>
                                        <th class="text-right text-danger h5 font-weight-bold">${{ number_format($totalAdeudo, 2) }}</th>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- PAGOS REALIZADOS -->
            <div class="row mt-5">
                <div class="col-12">
                    <h5 class="text-success border-bottom pb-2"><i class="fas fa-check-circle"></i> Historial de Pagos Recientes</h5>
                </div>
                <div class="col-12 table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Ticket / Ref</th>
                                <th>Fecha de Pago</th>
                                <th>Conceptos Pagados</th>
                                <th>Cajero</th>
                                <th class="text-right">Total Pagado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagosRecientes as $pago)
                                <tr class="{{ $pago->status == 'cancelado' ? 'text-muted bg-light' : '' }}">
                                    <td>
                                        #{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }} <br><small class="text-muted">{{ $pago->referencia_ticket }}</small>
                                        @if($pago->status == 'cancelado')
                                            <span class="badge badge-secondary ml-1">CANCELADO</span>
                                        @endif
                                    </td>
                                    <td>{{ $pago->fecha_pago->format('d/m/Y h:i A') }}</td>
                                    <td>
                                        <ul class="list-unstyled mb-0">
                                        @foreach($pago->detalles as $detalle)
                                            <li><small>{{ optional($detalle->adeudo)->tipo == 'colegiatura' ? 'Colegiatura ' . optional($detalle->adeudo)->mes_nombre . ' ' . optional($detalle->adeudo)->anio : (optional($detalle->adeudo)->concepto ?? 'Pago') }} - ${{ number_format($detalle->monto_pagado, 2) }}</small></li>
                                        @endforeach
                                        </ul>
                                    </td>
                                    <td>{{ $pago->cajero->name ?? 'Sistema' }}</td>
                                    <td class="text-right font-weight-bold {{ $pago->status == 'cancelado' ? 'text-muted' : 'text-success' }}">${{ number_format($pago->total, 2) }}</td>
                                    <td class="text-center">
                                        @if($pago->status != 'cancelado')
                                            <form action="{{ route('contabilidad.ventas.cancelar', $pago->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de cancelar este ticket de pago? Los adeudos volverán a estado pendiente.');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-danger" title="Cancelar Ticket de Pago">
                                                    <i class="fas fa-trash-alt"></i> Cancelar Ticket
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-muted"><small>Cancelado</small></span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center">No hay registros de pagos recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row no-print mt-4">
                <div class="col-12 text-center">
                    <button type="button" class="btn btn-default" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Estado de Cuenta
                    </button>
                    @if($totalAdeudo > 0)
                        <a href="{{ route('pagos.create', $alumno->id) }}" class="btn btn-success ml-2">
                            <i class="fas fa-cash-register"></i> Ir a Pagar en Caja
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop
