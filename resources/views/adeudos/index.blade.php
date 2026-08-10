@extends('adminlte::page')

@section('title', 'Historial de Adeudos')

@section('content_header')
<h1>Historial de Adeudos: {{ $alumno->nombre }} {{ $alumno->apellidos }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Listado de Adeudos Generados (Colegiaturas y Especiales)</h3>
        <div class="card-tools">
            <a href="{{ route('colegiaturas.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Volver a Colegiaturas
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table id="adeudos-table" class="table table-hover table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Tipo</th>
                    <th>Concepto / Periodo</th>
                    <th>Monto Base</th>
                    <th>Monto Actual</th>
                    <th>Monto Pagado</th>
                    <th>Estado</th>
                    <th>Fecha Pago</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @php 
                                                                $totalColegiatura = 0;
                    $totalEspecial = 0;
                @endphp
                @forelse($adeudos as $index => $adeudo)
                    <tr class="{{ $adeudo->status == 'cancelado' ? 'text-muted bg-light' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            @if($adeudo->tipo == 'colegiatura')
                                <span class="badge badge-primary">Colegiatura</span>
                            @else
                                <span class="badge badge-info">Especial</span>
                            @endif
                        </td>
                        <td>
                            @if($adeudo->tipo == 'colegiatura')
                                Periodo {{ $adeudo->periodo }}
                            @else
                                {{ $adeudo->concepto }}
                            @endif
                        </td>
                        <td>${{ number_format($adeudo->monto_base, 2) }}</td>
                        <td>
                            <strong>${{ number_format($adeudo->monto_calculado, 2) }}</strong>
                            @php 
                                                                                            if ($adeudo->status != 'pagado' && $adeudo->status != 'cancelado') {
                                    if ($adeudo->tipo == 'colegiatura')
                                        $totalColegiatura += $adeudo->monto_calculado;
                                    else
                                        $totalEspecial += $adeudo->monto_calculado;
                                }
                            @endphp
                        </td>
                        <td>
                            @if($adeudo->pagosDetalles && $adeudo->pagosDetalles->count() > 0)
                                @php $totalPagadoAdeudo = $adeudo->pagosDetalles->sum('monto_pagado'); @endphp
                                <span class="text-success font-weight-bold">${{ number_format($totalPagadoAdeudo, 2) }}</span>
                                @if($adeudo->pagosDetalles->count() > 1)
                                    <div><small class="text-muted">({{ $adeudo->pagosDetalles->count() }} abonos)</small></div>
                                @endif
                            @else
                                <span class="text-muted">---</span>
                            @endif
                        </td>
                        <td>
                            @if($adeudo->status == 'pagado')
                                <span class="badge badge-success">Pagado</span>
                            @elseif($adeudo->status == 'vencido')
                                <span class="badge badge-danger">Vencido</span>
                            @elseif($adeudo->status == 'cancelado')
                                <span class="badge badge-secondary">Cancelado</span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            @if($adeudo->pagosDetalles && $adeudo->pagosDetalles->count() > 0)
                                @foreach($adeudo->pagosDetalles as $det)
                                    @if($det->pago && $det->pago->fecha_pago)
                                        <div><small class="badge badge-light border">{{ \Carbon\Carbon::parse($det->pago->fecha_pago)->format('d/m/Y') }} (${{ number_format($det->monto_pagado, 2) }})</small></div>
                                    @endif
                                @endforeach
                            @elseif($adeudo->fecha_pago)
                                {{ \Carbon\Carbon::parse($adeudo->fecha_pago)->format('d/m/Y') }}
                            @else
                                <span class="text-muted">---</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($adeudo->pagosDetalles && $adeudo->pagosDetalles->count() > 0)
                                <div class="btn-group-vertical btn-group-sm">
                                    @foreach($adeudo->pagosDetalles as $det)
                                        @if($det->pago_id)
                                            <a href="{{ route('pagos.ticket', $det->pago_id) }}" target="_blank" class="btn btn-xs btn-outline-info my-1" title="Ticket #{{ $det->pago_id }}">
                                                <i class="fas fa-receipt"></i> Ticket (${{ number_format($det->monto_pagado, 2) }})
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            @if($adeudo->status != 'pagado' && $adeudo->status != 'cancelado')
                                <form action="{{ route('adeudos.cancelar', $adeudo->id) }}" method="POST" class="d-inline mt-1"
                                    onsubmit="return confirm('¿Está seguro de cancelar este adeudo?');">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Cancelar Adeudo">
                                        <i class="fas fa-ban"></i> Cancelar
                                    </button>
                                </form>
                            @elseif(!$adeudo->pagosDetalles || $adeudo->pagosDetalles->count() == 0)
                                <span class="badge badge-success"><i class="fas fa-check"></i> Pagado</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay adeudos registrados para este alumno.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-sm-4 border-right">
                <div class="description-block">
                    <h5 class="description-header text-primary">${{ number_format($totalColegiatura, 2) }}</h5>
                    <span class="description-text">DEUDA COLEGIATURA</span>
                </div>
            </div>
            <div class="col-sm-4 border-right">
                <div class="description-block">
                    <h5 class="description-header text-info">${{ number_format($totalEspecial, 2) }}</h5>
                    <span class="description-text">DEUDA ESPECIAL</span>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="description-block">
                    <h5 class="description-header text-danger font-weight-bold">
                        ${{ number_format($totalColegiatura + $totalEspecial, 2) }}</h5>
                    <span class="description-text">TOTAL PENDIENTE</span>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function () {
        $('#adeudos-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "order": [[0, "asc"]],
            "columnDefs": [
                { "orderable": false, "targets": 8 }
            ]
        });
    });
</script>
@stop