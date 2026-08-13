@extends('adminlte::page')

@section('title', 'Reporte de Adeudos Especiales')

@section('content_header')
    <h1>Auditoría de Adeudos Especiales por Concepto</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary no-print">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Filtrar por Concepto</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('reportes.adeudos_especiales') }}" method="GET" class="form-inline">
                    <label for="concepto" class="mr-2">Concepto Especial:</label>
                    <select name="concepto" id="concepto" class="form-control mr-3 select2" style="min-width: 300px;" required>
                        <option value="">-- Seleccione un concepto --</option>
                        @foreach($conceptos as $c)
                            <option value="{{ $c }}" {{ $conceptoSeleccionado == $c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Consultar</button>
                    @if($conceptoSeleccionado)
                        <button type="button" class="btn btn-secondary ml-2" onclick="window.print()"><i class="fas fa-print"></i> Imprimir Reporte</button>
                    @endif
                </form>
            </div>
        </div>

        @if($conceptoSeleccionado)
            <!-- Resumen Financiero del Concepto -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>${{ number_format($totalAsignado, 2) }}</h3>
                            <p>Total Asignado</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>${{ number_format($totalPagado, 2) }}</h3>
                            <p>Total Cobrado (Pagado)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>${{ number_format($totalPendiente, 2) }}</h3>
                            <p>Total Pendiente (Por Cobrar)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Listado de Alumnos -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-list mr-1"></i> Desglose de Alumnos: <span class="text-primary">{{ $conceptoSeleccionado }}</span>
                    </h3>
                </div>
                <div class="card-body">
                    @if($adeudos->count() > 0)
                        <table id="adeudos-especiales-table" class="table table-bordered table-striped table-hover text-sm w-100">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Alumno</th>
                                    <th>Grado y Grupo</th>
                                    <th class="text-right">Monto Base</th>
                                    <th class="text-right">Monto Actual</th>
                                    <th>Estado</th>
                                    <th>Fecha de Pago</th>
                                    <th class="no-print">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adeudos as $adeudo)
                                    <tr>
                                        <td><code>{{ $adeudo->alumno->matricula }}</code></td>
                                        <td>{{ $adeudo->alumno->apellido_paterno }} {{ $adeudo->alumno->apellido_materno }} {{ $adeudo->alumno->nombre }}</td>
                                        <td>{{ $adeudo->alumno->gradoGrupo->grado ?? '' }} "{{ $adeudo->alumno->gradoGrupo->grupo ?? '' }}"</td>
                                        <td class="text-right">${{ number_format($adeudo->monto_base, 2) }}</td>
                                        <td class="text-right font-weight-bold @if($adeudo->status != 'pagado' && $adeudo->status != 'cancelado') text-danger @endif">
                                            ${{ number_format($adeudo->monto_calculado, 2) }}
                                        </td>
                                        <td>
                                            @if($adeudo->status == 'pagado')
                                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Pagado</span>
                                            @elseif($adeudo->status == 'vencido')
                                                <span class="badge badge-danger"><i class="fas fa-times mr-1"></i> Vencido</span>
                                            @elseif($adeudo->status == 'cancelado')
                                                <span class="badge badge-secondary"><i class="fas fa-ban mr-1"></i> Cancelado</span>
                                            @else
                                                <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> Pendiente</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $adeudo->fecha_pago ? \Carbon\Carbon::parse($adeudo->fecha_pago)->format('d/m/Y h:i A') : '---' }}
                                        </td>
                                        <td class="no-print">
                                            @if($adeudo->status != 'pagado' && $adeudo->status != 'cancelado')
                                                <form action="{{ route('adeudos.cancelar', $adeudo->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Está seguro de cancelar el adeudo de {{ addslashes($adeudo->concepto) }} para {{ addslashes($adeudo->alumno->nombre . ' ' . $adeudo->alumno->apellido_paterno) }}? Ya no aparecerá pendiente en el POS.');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-outline-danger" title="Cancelar Adeudo">
                                                        <i class="fas fa-ban"></i> Cancelar
                                                    </button>
                                                </form>
                                            @elseif($adeudo->status == 'pagado')
                                                <span class="text-muted"><i class="fas fa-check-double text-success"></i> Liquidado</span>
                                            @else
                                                <span class="text-muted"><i class="fas fa-ban text-secondary"></i> Cancelado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-info text-center">No se encontraron registros de adeudos especiales para este concepto.</div>
                    @endif
                </div>
            </div>
        @else
            <div class="alert alert-info text-center py-4">
                <h4><i class="fas fa-info-circle mr-1"></i> Reporte de Adeudos Especiales</h4>
                <p class="mb-0">Por favor, seleccione un concepto de la parte superior para visualizar las estadísticas y los alumnos asignados.</p>
            </div>
        @endif
    </div>
</div>

<style>
    @media print {
        .no-print, .main-sidebar, .main-header, .content-header, form { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding-top: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        .card-header { background: transparent !important; border-bottom: 2px solid #333 !important; }
        .table { width: 100% !important; border-collapse: collapse !important; }
        .table th { background-color: #f2f2f2 !important; color: #000 !important; }
    }
</style>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        if ($('#adeudos-especiales-table').length) {
            $('#adeudos-especiales-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                },
                "pageLength": 25,
                "responsive": true,
                "columnDefs": [
                    { "orderable": false, "targets": 7 }
                ]
            });
        }
    });
</script>
@stop
