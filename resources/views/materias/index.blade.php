@extends('adminlte::page')

@section('title', 'Materias')

@section('content_header')
    <h1>Lista de Materias</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('materias.create') }}" class="btn btn-primary">Nueva Materia</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Grado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materias as $materia)
                    <tr>
                        <td>{{ $materia->id }}</td>
                        <td>{{ $materia->nombre }}</td>
                        <td>{{ $materia->grado ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('materias.edit', $materia) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('materias.destroy', $materia) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta materia?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
