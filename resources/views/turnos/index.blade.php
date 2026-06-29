@extends('adminlte::page')

@section('title', 'Turnos')

@section('content_header')
    <h1>Gestión de Turnos</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Listado de Turnos</h3>
                <div class="card-tools">
                    <a href="{{ route('turnos.create') }}" class="btn btn-sm btn-light">Nuevo Turno</a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Nombre del Turno</th>
                            <th>Hora de Inicio</th>
                            <th>Hora de Fin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($turnos as $turno)
                            <tr>
                                <td>{{ $turno->nombre }}</td>
                                <td>{{ \Carbon\Carbon::parse($turno->hora_inicio)->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($turno->hora_fin)->format('h:i A') }}</td>
                                <td>
                                    <a href="{{ route('turnos.edit', $turno->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('turnos.destroy', $turno->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este turno?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No hay turnos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
