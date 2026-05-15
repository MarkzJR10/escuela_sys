@extends('adminlte::page')

@section('title', 'Roles y Menús')

@section('content_header')
    <h1>Asignación de Menús por Rol</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('roles.create') }}" class="btn btn-primary">Nuevo Rol</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Menús Asignados</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->id }}</td>
                        <td>{{ ucfirst($role->name) }}</td>
                        <td>
                            @foreach($role->menus as $menu)
                                <span class="badge badge-info">{{ $menu->text }}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('roles.destroy', $role) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Seguro que deseas eliminar este rol?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
