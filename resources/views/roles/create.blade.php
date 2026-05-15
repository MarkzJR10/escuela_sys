@extends('adminlte::page')

@section('title', 'Nuevo Rol')

@section('content_header')
    <h1>Nuevo Rol</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="name">Nombre del Rol</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="Ej: supervisor" required>
                    <small class="text-muted">El nombre se guardará en minúsculas.</small>
                </div>

                <div class="form-group mt-4">
                    <label>Asignar Menús (Opcional):</label>
                    <div class="row mt-2">
                        @foreach($menus as $menu)
                            <div class="col-md-4">
                                <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                    <input type="checkbox" class="custom-control-input" name="menus[]" value="{{ $menu->id }}" id="menu_{{ $menu->id }}" {{ is_array(old('menus')) && in_array($menu->id, old('menus')) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="menu_{{ $menu->id }}">
                                        <i class="{{ $menu->icon }}"></i> {{ $menu->text }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Guardar Rol</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop
