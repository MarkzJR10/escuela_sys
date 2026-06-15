@extends('adminlte::page')

@section('title', 'Boletas - Buscar Alumno')

@section('content_header')
    <h1>Gestión de Boletas de Calificaciones</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Buscar Alumnos por Grado</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('boletas.index') }}" method="GET" class="mb-4">
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
                                <th width="350px">Acciones de Boleta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $alumno)
                                <tr>
                                    <td>{{ $alumno->matricula }}</td>
                                    <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('boletas.gestionar', $alumno->id) }}" class="btn btn-sm btn-info" title="Materias">
                                                <i class="fas fa-book"></i> Materias
                                            </a>
                                            <a href="{{ route('boletas.edit', $alumno->id) }}" class="btn btn-sm btn-warning" title="Capturar Calificaciones">
                                                <i class="fas fa-edit"></i> Capturar
                                            </a>
                                            <a href="{{ route('boletas.show', $alumno->id) }}" class="btn btn-sm btn-success" title="Ver Boleta">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                            <!-- Aquí irá el botón para generar el PDF (Fase 1/2) -->
                                        </div>
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
