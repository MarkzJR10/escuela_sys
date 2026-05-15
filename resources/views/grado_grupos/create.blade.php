@extends('adminlte::page')

@section('title', 'Nuevo Grado y Grupo')

@section('content_header')
    <h1>Nuevo Grado/Grupo</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('grado_grupos.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Grado (Ej. 1ro, 2do)</label>
                    <input type="text" name="grado" class="form-control" required value="{{ old('grado') }}">
                    @error('grado') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Grupo (Ej. A, B)</label>
                    <input type="text" name="grupo" class="form-control" required value="{{ old('grupo') }}">
                    @error('grupo') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="{{ route('grado_grupos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
