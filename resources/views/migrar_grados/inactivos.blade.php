@extends('adminlte::page')

@section('title', 'Alumnos Inactivos')

@section('content_header')
    <h1>Alumnos Inactivos / Suspendidos</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">Listado de Bajas</h3>
                <div class="card-tools">
                    <a href="{{ route('migrar_grados.index') }}" class="btn btn-sm btn-light">Volver a Migración</a>
                </div>
            </div>
            <div class="card-body">
                @if($alumnos->count() > 0)
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Matrícula</th>
                                <th>Alumno</th>
                                <th>Último Grado Registrado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $alumno)
                            <tr>
                                <td>{{ $alumno->matricula }}</td>
                                <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                                <td>{{ $alumno->gradoGrupo->grado ?? 'N/A' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"</td>
                                <td>
                                    <form action="{{ route('migrar_grados.reactivar') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Desea reactivar a este alumno?')">
                                            <i class="fas fa-user-check"></i> Reactivar Alumno
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">No hay alumnos inactivos o suspendidos en este momento.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
