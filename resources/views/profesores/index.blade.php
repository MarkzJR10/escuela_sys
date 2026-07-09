@extends('adminlte::page')

@section('title', 'Profesores')

@section('content_header')
    <h1>Gestión de Profesores</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('profesores.create') }}" class="btn btn-primary">Registrar Profesor</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table id="profesores-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre y Apellidos</th>
                        <th>Teléfono</th>
                        <th>Usuario (Login)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profesores as $profesor)
                    <tr>
                        <td>{{ $profesor->id }}</td>
                        <td>{{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}</td>
                        <td>{{ $profesor->telefono }}</td>
                        <td>
                            @if($profesor->user)
                                <span class="badge badge-info">{{ $profesor->user->email }}</span>
                            @else
                                <span class="badge badge-secondary">No Asignado</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('profesores.edit', $profesor) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('profesores.destroy', $profesor) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar a este profesor?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#profesores-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": 4 }
            ]
        });
    });
</script>
@stop
