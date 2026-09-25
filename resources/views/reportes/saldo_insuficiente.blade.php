@extends('adminlte::page')

@section('title', 'Reporte Saldo Insuficiente')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-exclamation-triangle text-warning mr-2"></i> Reporte de Pagos con Saldo Insuficiente</h1>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totales['total_registros'] }}</h3>
                <p>Pagos Insuficientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>${{ number_format($totales['total_abonado'], 2) }}</h3>
                <p>Total Abonado</p>
            </div>
            <div class="icon">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>${{ number_format($totales['total_debido'], 2) }}</h3>
                <p>Total que Debía Abonarse</p>
            </div>
            <div class="icon">
                <i class="fas fa-calculator"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>${{ number_format($totales['total_diferencia'], 2) }}</h3>
                <p>Diferencia Faltante</p>
            </div>
            <div class="icon">
                <i class="fas fa-minus-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-warning">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros y Búsqueda</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reportes.saldo_insuficiente') }}" class="row">
            <div class="col-md-5 mb-2">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar por Matrícula, Nombre o Referencia..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3 mb-2">
                <input type="text" name="periodo" class="form-control" placeholder="Periodo (YYYY-MM)" value="{{ request('periodo') }}">
            </div>
            <div class="col-md-4 mb-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Buscar</button>
                <a href="{{ route('reportes.saldo_insuficiente') }}" class="btn btn-secondary"><i class="fas fa-undo mr-1"></i> Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-danger">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Registros de Pagos Incompletos</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-bordered table-striped mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>Fecha Registro</th>
                    <th>Fecha Pago</th>
                    <th>Matrícula</th>
                    <th>Alumno</th>
                    <th>Grado</th>
                    <th>Periodo</th>
                    <th>Referencia / Leyenda</th>
                    <th class="text-right">Abonado</th>
                    <th class="text-right">Monto Requerido</th>
                    <th class="text-right text-danger">Faltante</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registros as $item)
                    <tr>
                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $item->fecha_pago ? \Carbon\Carbon::parse($item->fecha_pago)->format('d/m/Y') : 'N/A' }}</td>
                        <td><code>{{ $item->matricula ?? 'N/A' }}</code></td>
                        <td><strong>{{ $item->alumno_nombre ?? ($item->alumno->nombre_completo ?? 'N/A') }}</strong></td>
                        <td><span class="badge badge-info">{{ $item->grado ?? 'N/A' }}</span></td>
                        <td><span class="badge badge-secondary">{{ $item->periodo ?? 'N/A' }}</span></td>
                        <td>
                            <small class="d-block text-truncate" style="max-width: 250px;" title="{{ $item->referencia }} - {{ $item->referencia_leyenda }}">
                                <strong>Ref:</strong> {{ $item->referencia }}<br>
                                <strong>Leyenda:</strong> {{ $item->referencia_leyenda }}
                            </small>
                        </td>
                        <td class="text-right text-warning font-weight-bold">${{ number_format($item->monto_abonado, 2) }}</td>
                        <td class="text-right font-weight-bold">${{ number_format($item->monto_debido, 2) }}</td>
                        <td class="text-right text-danger font-weight-bold">${{ number_format($item->diferencia, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-check-circle text-success fa-2x d-block mb-2"></i>
                            No hay registros de pagos con saldo insuficiente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($registros->hasPages())
        <div class="card-footer clearfix">
            {{ $registros->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@stop
