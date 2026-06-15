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
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Matrícula</th>
                                <th>Alumno</th>
                                <th>Grado y Grupo</th>
                                <th width="150px">Acciones</th>
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
