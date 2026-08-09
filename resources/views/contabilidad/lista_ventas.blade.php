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
                    <label for="fecha_inicio" class="mr-2">Desde:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control mr-3" value="{{ $fechaInicio }}">
                    
                    <label for="fecha_fin" class="mr-2">Hasta:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control mr-3" value="{{ $fechaFin }}">
                    
                    <button type="submit" class="btn btn-dark"><i class="fas fa-search"></i> Buscar</button>
                </form>
            </div>
            
            <div class="card-body">
                @if($pagos->count() > 0)
                    <table id="ventas-table" class="table table-bordered table-striped table-hover text-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Folio Ticket</th>
                                <th>Fecha y Hora</th>
                                <th>Cajero</th>
                                <th>Padre / Tutor</th>
                                <th>Alumno(s)</th>
                                <th>Conceptos</th>
                                <th class="text-right">Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagos as $pago)
                            @php
                                // Obtener todos los alumnos asociados a los detalles del ticket
                                $alumnosDetalles = $pago->detalles->map(function($d) {
                                    return $d->adeudo ? $d->adeudo->alumno : null;
                                })->filter()->unique('id');

                                if ($alumnosDetalles->isEmpty() && $pago->alumno) {
                                    $alumnosDetalles = collect([$pago->alumno]);
                                }

                                // Obtener padre/tutor si existe
                                $padreObj = $pago->alumno->padre ?? null;
                            @endphp
                            <tr>
                                <td><span class="badge badge-info">#{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</span><br><small>{{ $pago->referencia_ticket }}</small></td>
                                <td>{{ $pago->fecha_pago->format('d/m/Y h:i A') }}</td>
                                <td>{{ $pago->cajero->name ?? 'Sistema' }}</td>
                                <td>
                                    @if($padreObj)
                                        <i class="fas fa-user-tie text-primary mr-1"></i><strong>{{ $padreObj->nombre }} {{ $padreObj->apellido_paterno }}</strong>
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                                <td>
                                    @if($alumnosDetalles->isNotEmpty())
                                        <ul class="list-unstyled mb-0 pl-0">
                                            @foreach($alumnosDetalles as $al)
                                                <li><i class="fas fa-user-graduate text-success mr-1"></i>{{ $al->nombre }} {{ $al->apellido_paterno }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">General</span>
                                    @endif
                                </td>
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
                    <div class="alert alert-info">No se encontraron ventas completadas para el rango de fechas seleccionado.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#ventas-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "order": [[0, "desc"]],
            "columnDefs": [
                { "orderable": false, "targets": 7 }
            ]
        });
    });
</script>
@stop
