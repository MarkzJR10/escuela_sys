@extends('adminlte::page')

@section('title', 'Pendientes por Mes')

@section('content_header')
    <h1>Colegiaturas Pendientes por Cobrar</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <form action="{{ route('reportes.pendientes_mes') }}" method="GET" class="form-inline">
                    <label for="mes" class="mr-2">Seleccionar Mes:</label>
                    <input type="month" name="mes" id="mes" class="form-control mr-2" value="{{ $mes }}">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Consultar</button>
                    <button type="button" class="btn btn-secondary ml-auto" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                </form>
            </div>
            
            <div class="card-body">
                <div class="alert alert-info text-center">
                    <h5>Total Pendiente de Cobro para <strong>{{ \Carbon\Carbon::parse($mes.'-01')->translatedFormat('F Y') }}</strong>: 
                    <span class="font-weight-bold text-lg">${{ number_format($total, 2) }}</span></h5>
                </div>

                @if($adeudos->count() > 0)
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Alumno</th>
                                <th>Grado y Grupo</th>
                                <th>Concepto</th>
                                <th class="text-right">Monto a Cobrar</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adeudos as $adeudo)
                                <tr>
                                    <td>{{ $adeudo->alumno->nombre }} {{ $adeudo->alumno->apellido_paterno }} {{ $adeudo->alumno->apellido_materno }}</td>
                                    <td>{{ $adeudo->alumno->gradoGrupo->grado ?? '' }} "{{ $adeudo->alumno->gradoGrupo->grupo ?? '' }}"</td>
                                    <td>{{ $adeudo->concepto }}</td>
                                    <td class="text-right text-danger font-weight-bold">${{ number_format($adeudo->monto_actual, 2) }}</td>
                                    <td>
                                        <a href="{{ route('pagos.create', $adeudo->alumno->id) }}" class="btn btn-sm btn-success"><i class="fas fa-cash-register"></i> Ir a Caja</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-success">
                        No hay colegiaturas pendientes registradas para este mes.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, .main-sidebar, .main-header, form { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .btn { display: none !important; }
    }
</style>
@stop
