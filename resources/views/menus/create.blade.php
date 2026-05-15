@extends('adminlte::page')

@section('title', 'Nuevo Menú')

@section('content_header')
    <h1>Crear Nuevo Menú</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('menus.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Texto (Nombre a mostrar)</label>
                    <input type="text" name="text" class="form-control" required value="{{ old('text') }}">
                    @error('text') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>URL (Ruta, ej: 'users')</label>
                    <input type="text" name="url" class="form-control" value="{{ old('url') }}">
                    @error('url') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Ícono (Clase FontAwesome, ej: 'fas fa-fw fa-home')</label>
                    <input type="text" name="icon" class="form-control" value="{{ old('icon') }}">
                    <small>Busca íconos en <a href="https://fontawesome.com/icons?d=gallery&m=free" target="_blank">FontAwesome</a></small>
                    @error('icon') <br><span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="{{ route('menus.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
