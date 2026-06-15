@extends('adminlte::page')

@section('title', 'Detalle de Reporte de Tarea')

@section('content_header')
    <h1>Detalle de Tarea Incumplida</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">Información del Reporte</h3>
                <div class="card-tools">
                    <span class="badge {{ $reporte->estatus == 'pendiente' ? 'badge-warning' : 'badge-success' }}">
                        {{ ucfirst($reporte->estatus) }}
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                <p><strong>Fecha:</strong> {{ $reporte->fecha->format('d/m/Y') }}</p>
                <p><strong>Alumno:</strong> {{ $reporte->alumno->nombre }} {{ $reporte->alumno->apellido_paterno }} (Matrícula: {{ $reporte->alumno->matricula }})</p>
                <p><strong>Grado y Grupo:</strong> {{ $reporte->alumno->gradoGrupo->grado ?? 'N/A' }} "{{ $reporte->alumno->gradoGrupo->grupo ?? 'N/A' }}"</p>
                <p><strong>Maestro que Reporta:</strong> {{ $reporte->usuario->name }}</p>
                <hr>
                <p><strong>Materia:</strong> {{ $reporte->materia ?? 'N/A' }}</p>
                <p><strong>Descripción:</strong></p>
                <div class="p-3 bg-light rounded border">
                    {{ $reporte->descripcion }}
                </div>
                
                <div class="text-center mt-4">
                    <a href="{{ route('reportes_tareas.index') }}" class="btn btn-primary">Volver al listado</a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
