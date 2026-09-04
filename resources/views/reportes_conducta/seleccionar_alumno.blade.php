@extends('adminlte::page')

@section('title', 'Seleccionar Alumno - Reporte de Conducta')

@section('content_header')
    <h1>Capturar Reporte de Conducta</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Buscar Alumno</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('reportes_conducta.seleccionar') }}" method="GET" class="mb-4">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="grado_grupo_id">Grado y Grupo</label>
                            <select name="grado_grupo_id" id="grado_grupo_id" class="form-control" required>
                                <option value="">Seleccione un grupo...</option>
                                @foreach($gradoGrupos as $grupo)
                                    <option value="{{ $grupo->id }}" {{ request('grado_grupo_id') == $grupo->id ? 'selected' : '' }}>
                                        {{ $grupo->grado }} "{{ $grupo->grupo }}"
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                        </div>
                    </div>
                </form>

                @if($alumnos->count() > 0)
                    <table id="alumnos-conducta-table" class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Matrícula</th>
                                <th>Alumno</th>
                                <th>Grado y Grupo</th>
                                <th width="180px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $alumno)
                                <tr>
                                    <td>{{ $alumno->matricula }}</td>
                                    <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                                    <td>{{ $alumno->gradoGrupo->grado }} "{{ $alumno->gradoGrupo->grupo }}"</td>
                                    <td>
                                        <a href="{{ route('reportes_conducta.create', ['alumno_id' => $alumno->id]) }}" class="btn btn-sm btn-success">
                                            <i class="fas fa-plus-circle"></i> Capturar Reporte
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif(request()->has('grado_grupo_id'))
                    <div class="alert alert-warning">
                        No se encontraron alumnos en este grupo.
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
        if ($('#alumnos-conducta-table').length) {
            $('#alumnos-conducta-table').DataTable({
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
                    { "orderable": false, "targets": 3 }
                ]
            });
        }
    });
</script>
@stop
