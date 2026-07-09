@extends('adminlte::page')

@section('title', 'Menús')

@section('content_header')
    <h1>Administrar Menús</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('menus.create') }}" class="btn btn-primary">Nuevo Menú</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table id="menus-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Texto (Nombre)</th>
                        <th>URL</th>
                        <th>Ícono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($menus as $menu)
                    <tr>
                        <td>{{ $menu->id }}</td>
                        <td>{{ $menu->text }}</td>
                        <td>{{ $menu->url }}</td>
                        <td><i class="{{ $menu->icon }}"></i> {{ $menu->icon }}</td>
                        <td>
                            <a href="{{ route('menus.edit', $menu) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('menus.destroy', $menu) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este menú?')">Eliminar</button>
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
        $('#menus-table').DataTable({
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
