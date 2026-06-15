@extends('adminlte::page')

@section('title', 'Capturar Reporte de Tarea')

@section('content_header')
    <h1>Nuevo Reporte de Tarea Incumplida</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Alumno: {{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</h3>
            </div>
            
            <form action="{{ route('reportes_tareas.store') }}" method="POST">
                @csrf
                <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="materia">Materia</label>
                        <input type="text" name="materia" id="materia" class="form-control" placeholder="Ej. Matemáticas" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción de la Tarea / Actividad</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="4" placeholder="Describa la tarea que no entregó el alumno..." required></textarea>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ url()->previous() }}" class="btn btn-default">Cancelar</a>
                    <button type="submit" class="btn btn-warning float-right"><i class="fas fa-save"></i> Guardar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
