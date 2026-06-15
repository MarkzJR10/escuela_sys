@extends('adminlte::page')

@section('title', 'Historial de Colegiaturas')

@section('content_header')
    <h1>Historial Completo de Colegiaturas</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Consultar por Grado y Grupo</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('reportes.historial_colegiaturas') }}" method="GET" class="mb-4 form-inline">
                    <label for="grado_grupo_id" class="mr-2">Grupo:</label>
                    <select name="grado_grupo_id" id="grado_grupo_id" class="form-control mr-2" required>
                        <option value="">Seleccione...</option>
                        @foreach($gradoGrupos as $g)
                            <option value="{{ $g->id }}" {{ request('grado_grupo_id') == $g->id ? 'selected' : '' }}>
                                {{ $g->grado }} "{{ $g->grupo }}"
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Consultar</button>
                    <button type="button" class="btn btn-secondary ml-auto" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                </form>

                @if(request()->has('grado_grupo_id'))
                    @if($adeudos->count() > 0)
                        <table class="table table-bordered table-striped table-sm text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Alumno</th>
                                    <th>Periodo (Mes)</th>
                                    <th>Concepto</th>
                                    <th>Monto Base</th>
                                    <th>Monto Final Cobrado/A Cobrar</th>
                                    <th>Estatus</th>
                                    <th>Fecha Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adeudos as $adeudo)
                                <tr>
                                    <td class="text-left">{{ $adeudo->alumno->apellido_paterno }} {{ $adeudo->alumno->nombre }}</td>
                                    <td>{{ $adeudo->periodo }}</td>
                                    <td>{{ $adeudo->concepto }}</td>
                                    <td>${{ number_format($adeudo->monto_base, 2) }}</td>
                                    <td class="font-weight-bold">${{ number_format($adeudo->monto_actual, 2) }}</td>
                                    <td>
                                        @if($adeudo->status == 'pagado')
                                            <span class="badge badge-success">Pagado</span>
                                        @else
                                            <span class="badge badge-danger">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>{{ $adeudo->fecha_pago ? \Carbon\Carbon::parse($adeudo->fecha_pago)->format('d/m/Y') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info">No hay colegiaturas registradas para este grupo.</div>
                    @endif
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
