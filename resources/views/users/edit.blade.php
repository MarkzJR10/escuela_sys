@extends('adminlte::page')

@section('title', 'Editar Usuario')

@section('content_header')
    <h1>Editar Usuario</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $user->name) }}">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required value="{{ old('email', $user->email) }}">
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Contraseña (Dejar en blanco para no cambiar)</label>
                    <input type="password" name="password" class="form-control">
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Rol</label>
                    <select name="role" class="form-control" required>
                        <option value="">-- Seleccione un Rol --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-success">Actualizar</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
