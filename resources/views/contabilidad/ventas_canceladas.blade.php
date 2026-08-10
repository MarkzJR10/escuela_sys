@extends('adminlte::page')

@section('title', 'Ventas Canceladas')

@section('content_header')
    <h1>Reporte de Ventas Canceladas</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-danger">
            <div class="card-header">
                <form action="{{ route('contabilidad.ventas_canceladas') }}" method="GET" class="form-inline">
                    <label for="fecha_inicio" class="mr-2">Desde:</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control mr-3" value="{{ $fechaInicio ?? $fecha }}">
                    <label for="fecha_fin" class="mr-2">Hasta:</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control mr-3" value="{{ $fechaFin ?? $fecha }}">
                    <button type="submit" class="btn btn-dark"><i class="fas fa-search"></i> Buscar Cancelados</button>
                </form>
            </div>
            
            <div class="card-body">
                @if($pagos->count() > 0)
                    <table class="table table-bordered table-striped text-sm">
                        <thead class="bg-danger text-white">
                            <tr>
                                <th>Folio Cancelado</th>
                                <th>Fecha Original</th>
                                <th>Fecha Cancelación</th>
                                <th>Cajero</th>
                                <th>Alumno</th>
                                <th class="text-right">Monto Cancelado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pagos as $pago)
                            <tr>
                                <td><del>#{{ str_pad($pago->id, 6, '0', STR_PAD_LEFT) }}</del></td>
                                <td>{{ $pago->fecha_pago->format('d/m/Y h:i A') }}</td>
                                <td class="text-danger font-weight-bold">{{ $pago->updated_at->format('d/m/Y h:i A') }}</td>
                                <td>{{ $pago->cajero->name ?? 'Sistema' }}</td>
                                <td>{{ $pago->alumno->nombre ?? 'Venta' }} {{ $pago->alumno->apellido_paterno ?? 'Público' }}</td>
                                <td class="text-right text-muted"><del>${{ number_format($pago->total, 2) }}</del></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-success">No hubo ventas canceladas en la fecha seleccionada.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
