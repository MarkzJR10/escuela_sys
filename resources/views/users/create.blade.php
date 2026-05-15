@extends('adminlte::page')

@section('title', 'Nuevo Usuario')

@section('content_header')
    <h1>Nuevo Usuario</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Rol</label>
                    <select name="role" class="form-control" required>
                        <option value="">-- Seleccione un Rol --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
