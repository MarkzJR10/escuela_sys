@extends('adminlte::page')

@section('title', 'Reportes Pendientes')

@section('content_header')
    <h1>Reportes de Conducta Pendientes (No Leídos)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Listado de reportes por revisar</h3>
            </div>
            
            <div class="card-body">
                @if($reportes->count() > 0)
                    <table id="reportes-pendientes-table" class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Alumno</th>
                                <th>Grado</th>
                                <th>Maestro que Reporta</th>
                                <th>No. Reporte</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportes as $reporte)
                            <tr>
                                <td>{{ $reporte->fecha->format('d/m/Y') }}</td>
                                <td>{{ $reporte->alumno->nombre }} {{ $reporte->alumno->apellido_paterno }}</td>
                                <td>{{ $reporte->alumno->gradoGrupo->grado ?? '' }} "{{ $reporte->alumno->gradoGrupo->grupo ?? '' }}"</td>
                                <td>{{ $reporte->usuario->name }}</td>
                                <td><span class="badge badge-info">Reporte #{{ $reporte->no_reporte }}</span></td>
                                <td>
                                    <a href="{{ route('reportes_conducta.show', $reporte->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-envelope-open-text"></i> Abrir / Leer
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Todos los reportes han sido leídos. No hay pendientes.
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
        if ($('#reportes-pendientes-table').length) {
            $('#reportes-pendientes-table').DataTable({
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
                    { "orderable": false, "targets": 5 }
                ]
            });
        }
    });
</script>
@stop
