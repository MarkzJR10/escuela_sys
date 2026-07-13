@extends('adminlte::page')

@section('title', 'Ventas por Producto')

@section('content_header')
    <h1>Ventas de Productos por Fecha</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <form action="{{ route('contabilidad.ventas_producto') }}" method="GET" class="form-inline">
                    <label for="fecha" class="mr-2">Seleccionar Fecha:</label>
                    <input type="date" name="fecha" id="fecha" class="form-control mr-2" value="{{ $fecha }}">
                    <button type="submit" class="btn btn-dark"><i class="fas fa-search"></i> Consultar</button>
                    <button type="button" class="btn btn-secondary ml-auto" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                </form>
            </div>
            
            <div class="card-body">
                @if($detalles->count() > 0)
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>Concepto / Producto</th>
                                <th class="text-center">Cantidad de Operaciones</th>
                                <th class="text-right">Total Vendido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $granTotal = 0; @endphp
                            @foreach($detalles as $detalle)
                            <tr>
                                <td>{{ $detalle->concepto }}</td>
                                <td class="text-center">{{ $detalle->cantidad_tickets }}</td>
                                <td class="text-right">${{ number_format($detalle->total_vendido, 2) }}</td>
                            </tr>
                            @php $granTotal += $detalle->total_vendido; @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total General:</th>
                                <th class="text-right h5 text-danger">${{ number_format($granTotal, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="alert alert-info">No se registraron ventas de productos o conceptos en esta fecha.</div>
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
