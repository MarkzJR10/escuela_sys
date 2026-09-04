@extends('adminlte::page')

@section('title', 'Reportes de Conducta por Día')

@section('content_header')
    <h1>Reportes de Conducta del Día</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <form action="{{ route('reportes_conducta.index') }}" method="GET" class="form-inline">
                    <label class="mr-2" for="fecha">Consultar fecha:</label>
                    <input type="date" name="fecha" id="fecha" class="form-control mr-2" value="{{ $fecha }}">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Consultar</button>
                </form>
            </div>
            
            <div class="card-body">
                @if($reportes->count() > 0)
                    <table id="reportes-conducta-table" class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Alumno</th>
                                <th>Grado</th>
                                <th>Maestro</th>
                                <th>No. Reporte</th>
                                <th>Estatus</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportes as $reporte)
                            <tr>
                                <td>{{ $reporte->id }}</td>
                                <td>{{ $reporte->alumno->nombre }} {{ $reporte->alumno->apellido_paterno }}</td>
                                <td>{{ $reporte->alumno->gradoGrupo->grado ?? '' }} "{{ $reporte->alumno->gradoGrupo->grupo ?? '' }}"</td>
                                <td>{{ $reporte->usuario->name }}</td>
                                <td><span class="badge badge-info">Reporte #{{ $reporte->no_reporte }}</span></td>
                                <td>
                                    @if($reporte->estatus == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @else
                                        <span class="badge badge-success">Leído</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('reportes_conducta.show', $reporte->id) }}" class="btn btn-sm btn-info" title="Ver Detalle">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="{{ route('reportes_conducta.por_alumno', $reporte->alumno_id) }}" class="btn btn-sm btn-secondary" title="Historial del Alumno">
                                        <i class="fas fa-history"></i> Historial
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">
                        No hay reportes de conducta para la fecha seleccionada.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        if ($('#reportes-conducta-table').length) {
            $('#reportes-conducta-table').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "pageLength": 10,
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sSearch":         "Buscar:",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    }
                },
                "columnDefs": [
                    { "orderable": false, "targets": 6 }
                ]
            });
        }
    });
</script>
@stop
