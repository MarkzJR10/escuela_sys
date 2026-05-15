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
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Tipo</th>
                        <th>Concepto / Periodo</th>
                        <th>Monto Base</th>
                        <th>Monto Actual</th>
                        <th>Estado</th>
                        <th>Fecha Pago</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalColegiatura = 0;
                        $totalEspecial = 0;
                    @endphp
                    @forelse($adeudos as $index => $adeudo)
                        <tr>
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
                                    if($adeudo->status != 'pagado') {
                                        if($adeudo->tipo == 'colegiatura') $totalColegiatura += $adeudo->monto_calculado;
                                        else $totalEspecial += $adeudo->monto_calculado;
                                    }
                                @endphp
                            </td>
                            <td>
                                @if($adeudo->status == 'pagado')
                                    <span class="badge badge-success">Pagado</span>
                                @elseif($adeudo->status == 'vencido')
                                    <span class="badge badge-danger">Vencido</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </td>
                            <td>{{ $adeudo->fecha_pago ? Carbon\Carbon::parse($adeudo->fecha_pago)->format('d/m/Y') : '---' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay adeudos registrados para este alumno.</td>
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
                        <h5 class="description-header text-danger font-weight-bold">${{ number_format($totalColegiatura + $totalEspecial, 2) }}</h5>
                        <span class="description-text">TOTAL PENDIENTE</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
