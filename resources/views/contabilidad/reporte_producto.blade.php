@extends('adminlte::page')

@section('title', 'Reporte por Producto')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-boxes text-primary mr-2"></i>Reporte por Producto</h1>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <!-- Card de Filtros -->
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtros de Búsqueda</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('contabilidad.reporte_producto') }}" method="GET" class="row">
                    <div class="form-group col-md-5">
                        <label for="producto_id"><i class="fas fa-box text-secondary mr-1"></i> Seleccionar Producto:</label>
                        <select name="producto_id" id="producto_id" class="form-control select2" style="width: 100%;">
                            <option value="">-- Todos los Productos --</option>
                            @foreach($productos as $prod)
                                <option value="{{ $prod->id }}" {{ (string)$productoId === (string)$prod->id ? 'selected' : '' }}>
                                    {{ $prod->nombre }} (Stock actual: {{ $prod->stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-3">
                        <label for="fecha_inicio"><i class="far fa-calendar-alt text-secondary mr-1"></i> Desde:</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ $fechaInicio }}">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="fecha_fin"><i class="far fa-calendar-alt text-secondary mr-1"></i> Hasta:</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ $fechaFin }}">
                    </div>

                    <div class="form-group col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block" title="Buscar">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas de Resumen KPI -->
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>{{ number_format($totalCantidadVendida) }} <small class="text-white" style="font-size: 16px;">unidades</small></h3>
                <p>Cantidad Total Vendida en Rango</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box {{ $selectedProducto ? 'bg-success' : 'bg-secondary' }} shadow-sm">
            <div class="inner">
                <h3>
                    @if($selectedProducto)
                        {{ number_format($selectedProducto->stock) }} <small class="text-white" style="font-size: 16px;">unidades</small>
                    @else
                        N/A
                    @endif
                </h3>
                <p>Stock Físico / Sistema Actual {{ $selectedProducto ? '(' . $selectedProducto->nombre . ')' : '(Seleccione Producto)' }}</p>
            </div>
            <div class="icon">
                <i class="fas fa-warehouse"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-12">
        <div class="small-box bg-primary shadow-sm">
            <div class="inner">
                <h3>${{ number_format($totalMontoVendido, 2) }}</h3>
                <p>Total Recaudado en el Periodo</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i> Detalle de Ventas 
                    @if($selectedProducto)
                        de <strong>{{ $selectedProducto->nombre }}</strong>
                    @endif
                </h3>
                <div class="card-tools ml-auto">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Imprimir Reporte
                    </button>
                </div>
            </div>

            <div class="card-body">
                @if($ventas->count() > 0)
                    <div class="table-responsive">
                        <table id="reporte-producto-table" class="table table-bordered table-striped text-sm">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>Descripcion</th>
                                    <th class="text-center" style="width: 140px;">Cantidad vendida</th>
                                    <th class="text-center" style="width: 160px;">Fecha</th>
                                    <th>Usuario que vendio</th>
                                    <th class="text-center" style="width: 150px;">Ticket asociado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventas as $venta)
                                <tr>
                                    <td>
                                        <strong>{{ $venta->descripcion }}</strong>
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        <span class="badge badge-primary px-3 py-2" style="font-size: 13px;">
                                            {{ $venta->cantidad }} {{ $venta->cantidad == 1 ? 'unidad' : 'unidades' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <i class="far fa-clock text-muted mr-1"></i>{{ $venta->fecha }}
                                    </td>
                                    <td>
                                        <i class="fas fa-user-tag text-info mr-1"></i>{{ $venta->cajero }}
                                    </td>
                                    <td class="text-center">
                                        @if($venta->pago_id)
                                            <a href="{{ route('pagos.ticket', $venta->pago_id) }}" target="_blank" class="btn btn-xs btn-info" title="Ver / Imprimir Ticket">
                                                <i class="fas fa-receipt mr-1"></i> #{{ $venta->ticket }}
                                            </a>
                                        @else
                                            <span class="badge badge-light">Sin Ticket</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light font-weight-bold">
                                    <td class="text-right">Total Acumulado:</td>
                                    <td class="text-center text-primary h6 mb-0">{{ number_format($totalCantidadVendida) }} u.</td>
                                    <td colspan="2"></td>
                                    <td class="text-center text-success h6 mb-0">${{ number_format($totalMontoVendido, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-1"></i> No se encontraron ventas registradas para el criterio de búsqueda seleccionado en el periodo del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, .main-sidebar, .main-header, form, .card-header, .btn, .card-tools { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@stop

@section('plugins.Select2', true)
@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "-- Seleccionar Producto --",
            allowClear: true,
            theme: 'bootstrap4'
        });

        $('#reporte-producto-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 25,
            "responsive": true,
            "order": [[2, "desc"]], // Ordenar por fecha descendente
            "columnDefs": [
                { "orderable": false, "targets": [4] }
            ]
        });
    });
</script>
@stop
