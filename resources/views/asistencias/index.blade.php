@extends('adminlte::page')

@section('title', 'Pase de Lista')

@section('content_header')
    <h1>Pase de Lista Grupal</h1>
@stop

@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <form action="{{ route('asistencias.index') }}" method="GET" class="row">
                <div class="col-md-4">
                    <label>Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" onchange="this.form.submit()">
                </div>
                <div class="col-md-4">
                    <label>Grado y Grupo</label>
                    <select name="grado_grupo_id" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Seleccione Grupo --</option>
                        @foreach($gradoGrupos as $gradoGrupo)
                            <option value="{{ $gradoGrupo->id }}" {{ $grado_grupo_id == $gradoGrupo->id ? 'selected' : '' }}>
                                {{ $gradoGrupo->grado }} - {{ $gradoGrupo->grupo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-info btn-block">Buscar Lista</button>
                </div>
            </form>
        </div>
        
        @if($grado_grupo_id)
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('asistencias.store') }}" method="POST">
                @csrf
                <input type="hidden" name="fecha" value="{{ $fecha }}">
                <input type="hidden" name="grado_grupo_id" value="{{ $grado_grupo_id }}">

                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Alumno</th>
                            <th class="text-center" style="width: 400px;">Estado de Asistencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alumnos as $alumno)
                        <tr>
                            <td>{{ $alumno->nombre }} {{ $alumno->apellidos }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-success btn-sm {{ $alumno->asistencia_estado == 'Presente' ? 'active' : '' }}">
                                        <input type="radio" name="asistencias[{{ $alumno->id }}]" value="Presente" {{ $alumno->asistencia_estado == 'Presente' ? 'checked' : '' }}> Presente
                                    </label>
                                    <label class="btn btn-outline-danger btn-sm {{ $alumno->asistencia_estado == 'Ausente' ? 'active' : '' }}">
                                        <input type="radio" name="asistencias[{{ $alumno->id }}]" value="Ausente" {{ $alumno->asistencia_estado == 'Ausente' ? 'checked' : '' }}> Ausente
                                    </label>
                                    <label class="btn btn-outline-warning btn-sm {{ $alumno->asistencia_estado == 'Retardo' ? 'active' : '' }}">
                                        <input type="radio" name="asistencias[{{ $alumno->id }}]" value="Retardo" {{ $alumno->asistencia_estado == 'Retardo' ? 'checked' : '' }}> Retardo
                                    </label>
                                    <label class="btn btn-outline-info btn-sm {{ $alumno->asistencia_estado == 'Justificado' ? 'active' : '' }}">
                                        <input type="radio" name="asistencias[{{ $alumno->id }}]" value="Justificado" {{ $alumno->asistencia_estado == 'Justificado' ? 'checked' : '' }}> Justificado
                                    </label>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center">No hay alumnos registrados en este grupo.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @if(count($alumnos) > 0)
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save"></i> Guardar Pase de Lista
                    </button>
                </div>
                @endif
            </form>
        </div>
        @else
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-users fa-3x mb-3"></i>
            <p>Seleccione un grupo para comenzar el pase de lista.</p>
        </div>
        @endif
    </div>
@stop

@section('css')
<style>
    .btn-group-toggle .btn input[type="radio"] {
        position: absolute;
        clip: rect(0,0,0,0);
        pointer-events: none;
    }
</style>
@stop
