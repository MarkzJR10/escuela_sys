@extends('adminlte::page')

@section('title', 'Efectivo en Cajas')

@section('content_header')
    <h1>Corte de Caja (Efectivo por Cajero)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-warning">
            <div class="card-header">
                <form action="{{ route('contabilidad.efectivo_cajas') }}" method="GET" class="form-inline">
                    <label for="fecha" class="mr-2">Fecha del Corte:</label>
                    <input type="date" name="fecha" id="fecha" class="form-control mr-2" value="{{ $fecha }}">
                    <button type="submit" class="btn btn-dark"><i class="fas fa-calculator"></i> Generar Corte</button>
                    <button type="button" class="btn btn-secondary ml-auto" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                </form>
            </div>
            
            <div class="card-body">
                @if($cajas->count() > 0)
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Cajero / Usuario</th>
                                <th class="text-center">Tickets Cobrados</th>
                                <th class="text-right">Total en Caja (Sistema)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $granTotal = 0; @endphp
                            @foreach($cajas as $caja)
                            <tr>
                                <td>{{ $caja->cajero->name ?? 'Sistema' }}</td>
                                <td class="text-center">{{ $caja->total_tickets }}</td>
                                <td class="text-right font-weight-bold text-success">${{ number_format($caja->total_cobrado, 2) }}</td>
                            </tr>
                            @php $granTotal += $caja->total_cobrado; @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total General del Día:</th>
                                <th class="text-right h4 text-primary">${{ number_format($granTotal, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="alert alert-warning">No hay ingresos registrados para la fecha seleccionada.</div>
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
