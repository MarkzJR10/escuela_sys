@extends('adminlte::page')

@section('title', 'Gestionar Boleta')

@section('content_header')
    <h1>Gestionar Materias en Boleta</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Alumno: {{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</h3>
            </div>
            <div class="card-body">
                <p><strong>Matrícula:</strong> {{ $alumno->matricula }}</p>
                <p><strong>Grado:</strong> {{ $alumno->gradoGrupo->grado ?? 'N/A' }} "{{ $alumno->gradoGrupo->grupo ?? 'N/A' }}"</p>

                <hr>
                <h5>Agregar Materia a la Boleta</h5>
                <form action="{{ route('boletas.store_materia', $alumno->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="materia">Nombre de la Materia</label>
                        <input type="text" name="materia" id="materia" class="form-control" placeholder="Ej. Español, Matemáticas" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Agregar Materia</button>
                    <a href="{{ route('boletas.index') }}" class="btn btn-default float-right">Volver</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-secondary">
                <h3 class="card-title">Materias Actuales en la Boleta</h3>
            </div>
            <div class="card-body">
                @if(count($boletasExistentes) > 0)
                    <ul class="list-group">
                        @foreach($boletasExistentes as $mat)
                            <li class="list-group-item"><i class="fas fa-check text-success"></i> {{ $mat }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="alert alert-info">Aún no hay materias registradas para este alumno.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
