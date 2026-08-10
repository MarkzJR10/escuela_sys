@extends('adminlte::page')

@section('title', 'Estados de Cuenta')

@section('content_header')
    <h1>Estados de Cuenta (Adeudos por Alumno)</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado Global de Alumnos</h3>
        </div>
        <div class="card-body p-0">
            <table id="cartera-table" class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre Completo</th>
                        <th>Grado y Grupo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                        <tr>
                            <td><code>{{ $alumno->matricula }}</code></td>
                            <td>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                            <td>{{ $alumno->gradoGrupo->grado }} {{ $alumno->gradoGrupo->grupo }}</td>
                            <td class="text-center">
                                <a href="{{ route('colegiaturas.adeudos', $alumno->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Ver Adeudos
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron alumnos con esos criterios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#cartera-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": 3 }
            ]
        });
    });
</script>
@stop
