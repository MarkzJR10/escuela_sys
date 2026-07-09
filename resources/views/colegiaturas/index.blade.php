@extends('adminlte::page')

@section('title', 'Gestión de Colegiaturas')

@section('content_header')
    <h1>Gestión de Colegiaturas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Alumnos y Montos</h3>
        </div>
        <div class="card-body p-0">
            <table id="colegiaturas-table" class="table table-hover">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Grado y Grupo</th>
                        <th>Monto Actual</th>
                        <th>Acción</th>
                        <th>Historial</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                        <tr class="{{ is_null($alumno->colegiatura) ? 'table-warning' : '' }}">
                            <td>{{ $alumno->nombre }} {{ $alumno->apellidos }}</td>
                            <td>{{ $alumno->gradoGrupo->grado }} {{ $alumno->gradoGrupo->grupo }}</td>
                            <td>
                                <form action="{{ route('colegiaturas.update', $alumno) }}" method="POST" class="form-inline">
                                    @csrf
                                    @method('PATCH')
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="colegiatura" step="0.01" class="form-control" value="{{ $alumno->colegiatura }}" placeholder="Sin asignar">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                            <td>
                                @if(is_null($alumno->colegiatura))
                                    <span class="badge badge-danger">Pendiente de asignar</span>
                                @else
                                    <span class="badge badge-success">Asignado</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('colegiaturas.adeudos', $alumno) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-file-invoice-dollar"></i> Ver Adeudos
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No se encontraron alumnos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-warning { background-color: #fff3cd !important; }
    </style>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#colegiaturas-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": [2, 3, 4] } // Disable ordering on form field, status and actions
            ]
        });
    });
</script>
@stop
