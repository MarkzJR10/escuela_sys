@extends('adminlte::page')

@section('title', 'Gastos y Retiros')

@section('content_header')
    <h1>Registro de Gastos y Retiros de Caja</h1>
@stop

@section('content')
<div class="row">
    <!-- Registrar Nuevo Gasto -->
    <div class="col-md-4">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Nuevo Gasto / Retiro</h3>
            </div>
            <form action="{{ route('contabilidad.gastos.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Concepto</label>
                        <input type="text" name="concepto" class="form-control" placeholder="Ej. Compra de material, Pago a proveedor..." required>
                    </div>
                    <div class="form-group">
                        <label>Monto ($)</label>
                        <input type="number" step="0.01" name="monto" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Observaciones (Opcional)</label>
                        <textarea name="observaciones" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning btn-block"><i class="fas fa-file-invoice-dollar"></i> Registrar Gasto</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Historial de Gastos del Día -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Gastos del Día</h3>
                <div class="card-tools">
                    <form action="{{ route('contabilidad.gastos.index') }}" method="GET" class="form-inline">
                        <input type="date" name="fecha" class="form-control form-control-sm mr-2" value="{{ $fecha }}">
                        <button type="submit" class="btn btn-sm btn-primary">Ver Fecha</button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-sm text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Registrado por</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gastos as $g)
                        <tr>
                            <td class="text-left font-weight-bold">{{ $g->concepto }}</td>
                            <td class="text-danger">${{ number_format($g->monto, 2) }}</td>
                            <td>{{ $g->cajero->name }}</td>
                            <td class="text-left text-muted small">{{ $g->observaciones }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-4 text-info">No se han registrado gastos en esta fecha.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($totalGastos > 0)
                    <tfoot>
                        <tr>
                            <th class="text-right">Total de Gastos:</th>
                            <th class="text-danger h5">${{ number_format($totalGastos, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@stop
