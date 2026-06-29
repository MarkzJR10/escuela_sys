@extends('adminlte::page')

@section('title', 'Editar Turno')

@section('content_header')
    <h1>Editar Turno</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card card-primary">
            <form action="{{ route('turnos.update', $turno->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Nombre del Turno</label>
                        <input type="text" name="nombre" class="form-control" value="{{ $turno->nombre }}" required>
                    </div>
                    <div class="form-group">
                        <label>Hora de Inicio</label>
                        <input type="time" name="hora_inicio" class="form-control" value="{{ substr($turno->hora_inicio, 0, 5) }}" required>
                    </div>
                    <div class="form-group">
                        <label>Hora de Fin</label>
                        <input type="time" name="hora_fin" class="form-control" value="{{ substr($turno->hora_fin, 0, 5) }}" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary float-right">Actualizar</button>
                    <a href="{{ route('turnos.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
