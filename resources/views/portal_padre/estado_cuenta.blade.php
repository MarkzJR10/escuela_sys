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
            <div class="card-body p-4">
                @php $hasAdeudos = false; @endphp

                <!-- COLEGIATURAS -->
                @if($colegiaturas->isNotEmpty())
                    @php $hasAdeudos = true; @endphp
                    <h6 class="text-primary font-weight-bold"><i class="fas fa-book mr-1"></i> Colegiaturas</h6>
                    <table class="table table-striped table-hover mb-4">
                        <thead class="thead-dark">
                            <tr>
                                <th>Concepto</th>
                                <th>Periodo</th>
                                <th>Estado</th>
                                <th class="text-right" style="width: 150px;">Monto a Pagar</th>
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
                                        ${{ number_format($adeudo->monto_actual, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <!-- ADEUDOS ESPECIALES -->
                @if($especiales->isNotEmpty())
                    @php $hasAdeudos = true; @endphp
                    <h6 class="text-info font-weight-bold"><i class="fas fa-star mr-1"></i> Adeudos Especiales</h6>
                    <table class="table table-striped table-hover mb-4">
                        <thead class="thead-dark">
                            <tr>
                                <th>Concepto</th>
                                <th>Estado</th>
                                <th class="text-right" style="width: 150px;">Monto a Pagar</th>
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
                                        ${{ number_format($adeudo->monto_actual, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <!-- VENTAS Y PRODUCTOS -->
                @if($ventas->isNotEmpty())
                    @php $hasAdeudos = true; @endphp
                    <h6 class="text-success font-weight-bold"><i class="fas fa-shopping-cart mr-1"></i> Ventas y Consumos (Crédito)</h6>
                    <table class="table table-striped table-hover mb-4">
                        <thead class="thead-dark">
                            <tr>
                                <th>Concepto</th>
                                <th>Estado</th>
                                <th class="text-right" style="width: 150px;">Monto a Pagar</th>
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
                                        ${{ number_format($adeudo->monto_actual, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if(!$hasAdeudos)
                    <div class="alert alert-success text-center py-4"><i class="fas fa-check-circle mr-1"></i> No tiene adeudos registrados.</div>
                @else
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <table class="table table-bordered">
                                <tr class="bg-light">
                                    <th class="text-right">Total General Pendiente:</th>
                                    <th class="text-right text-danger h5 font-weight-bold">${{ number_format($totalAdeudo, 2) }}</th>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('portal_padre.dashboard') }}" class="btn btn-default">Volver al Panel</a>
            </div>
        </div>
    </div>
</div>
@stop
