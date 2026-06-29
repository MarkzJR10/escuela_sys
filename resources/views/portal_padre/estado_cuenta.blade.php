@extends('adminlte::page')

@section('title', 'Estado de Cuenta')

@section('content_header')
    <h1>Estado de Cuenta: {{ $alumno->nombre }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Saldos Pendientes</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Concepto</th>
                            <th>Periodo</th>
                            <th>Estado</th>
                            <th class="text-right">Monto a Pagar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adeudosAnteriores as $adeudo)
                            <tr>
                                <td>{{ $adeudo->concepto }} <span class="badge badge-danger">Vencido</span></td>
                                <td>{{ $adeudo->periodo }}</td>
                                <td>Pendiente</td>
                                <td class="text-right text-danger font-weight-bold">${{ number_format($adeudo->monto_actual, 2) }}</td>
                            </tr>
                        @endforeach
                        @foreach($adeudosActuales as $adeudo)
                            <tr>
                                <td>{{ $adeudo->concepto }}</td>
                                <td>{{ $adeudo->periodo }}</td>
                                <td>Pendiente</td>
                                <td class="text-right">${{ number_format($adeudo->monto_actual, 2) }}</td>
                            </tr>
                        @endforeach
                        @if($adeudosAnteriores->isEmpty() && $adeudosActuales->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-success p-4">No tiene adeudos registrados.</td>
                            </tr>
                        @else
                            <tr class="bg-light">
                                <th colspan="3" class="text-right">Total a Pagar:</th>
                                <th class="text-right text-danger h5">${{ number_format($totalAdeudo, 2) }}</th>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('portal_padre.dashboard') }}" class="btn btn-default">Volver al Panel</a>
            </div>
        </div>
    </div>
</div>
@stop
