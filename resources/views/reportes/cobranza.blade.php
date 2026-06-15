@extends('adminlte::page')

@section('title', 'Reporte de Cobranza')

@section('content_header')
    <h1>Reporte General de Cobranza</h1>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>${{ number_format($totalColegiaturas, 2) }}</h3>
                <p>Colegiaturas Pendientes</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>${{ number_format($totalEspeciales, 2) }}</h3>
                <p>Adeudos Especiales</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>${{ number_format($totalVentasCredito, 2) }}</h3>
                <p>Ventas a Crédito (Tienda)</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>${{ number_format($totalGeneral, 2) }}</h3>
                <p>Total Cartera Vencida</p>
            </div>
            <div class="icon"><i class="fas fa-wallet"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-dark">
        <h3 class="card-title">Detalle de Deuda por Alumno</h3>
        <div class="card-tools">
            <button class="btn btn-success btn-sm" onclick="window.print()"><i class="fas fa-print"></i> Imprimir Reporte</button>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped table-hover data-table">
            <thead class="thead-light">
                <tr>
                    <th>Alumno</th>
                    <th>Grado</th>
                    <th class="text-right">Colegiaturas</th>
                    <th class="text-right">Especiales</th>
                    <th class="text-right">Créditos</th>
                    <th class="text-right bg-danger text-white">Saldo Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alumnos as $a)
                    @if($a->saldo_total > 0)
                    <tr>
                        <td>{{ $a->apellido_paterno }} {{ $a->apellido_materno }} {{ $a->nombre }}</td>
                        <td>{{ $a->gradoGrupo->grado ?? '' }} "{{ $a->gradoGrupo->grupo ?? '' }}"</td>
                        <td class="text-right">${{ number_format($a->colegiaturas_pendientes, 2) }}</td>
                        <td class="text-right">${{ number_format($a->adeudos_especiales, 2) }}</td>
                        <td class="text-right">${{ number_format($a->creditos, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">${{ number_format($a->saldo_total, 2) }}</td>
                        <td>
                            <a href="{{ route('reportes.detalle_alumno', $a->id) }}" class="btn btn-xs btn-primary"><i class="fas fa-search"></i> Ver Estado de Cuenta</a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<style>
    @media print {
        .no-print, .main-sidebar, .main-header { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .btn { display: none !important; }
    }
</style>
@stop
