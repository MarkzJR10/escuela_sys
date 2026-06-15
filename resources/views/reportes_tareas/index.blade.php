@extends('adminlte::page')

@section('title', 'Reportes de Tareas')

@section('content_header')
    <h1>Reportes de Tareas Incumplidas</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <form action="{{ route('reportes_tareas.index') }}" method="GET" class="form-inline">
                    <label class="mr-2" for="fecha">Consultar fecha:</label>
                    <input type="date" name="fecha" id="fecha" class="form-control mr-2" value="{{ $fecha }}">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Consultar</button>
                </form>
            </div>
            
            <div class="card-body">
                @if($reportes->count() > 0)
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Alumno</th>
                                <th>Grado</th>
                                <th>Materia</th>
                                <th>Maestro</th>
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
                                <td>{{ $reporte->materia ?? 'N/A' }}</td>
                                <td>{{ $reporte->usuario->name }}</td>
                                <td>
                                    @if($reporte->estatus == 'pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @else
                                        <span class="badge badge-success">Leído</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('reportes_tareas.show', $reporte->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">
                        No hay reportes de tareas para la fecha seleccionada.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
