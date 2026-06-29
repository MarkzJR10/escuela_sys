@extends('adminlte::page')

@section('title', 'Crear Turno')

@section('content_header')
    <h1>Nuevo Turno</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-primary">
            <form action="{{ route('turnos.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Nombre del Turno</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. Matutino, Vespertino" required>
                    </div>
                    <div class="form-group">
                        <label>Hora de Inicio</label>
                        <input type="time" name="hora_inicio" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Hora de Fin</label>
                        <input type="time" name="hora_fin" class="form-control" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary float-right">Guardar</button>
                    <a href="{{ route('turnos.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
