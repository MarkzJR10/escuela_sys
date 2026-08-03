@extends('adminlte::page')

@section('title', 'Catálogo de Colegiaturas')

@section('content_header')
    <h1>Catálogo de Colegiaturas</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('colegiaturas-config.create') }}" class="btn btn-primary">Nueva Colegiatura</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <table id="colegiaturas-config-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Monto Mensual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($colegiaturas as $colegiatura)
                    <tr>
                        <td>{{ $colegiatura->id }}</td>
                        <td>{{ $colegiatura->nombre }}</td>
                        <td>${{ number_format($colegiatura->monto, 2) }}</td>
                        <td>
                            <a href="{{ route('colegiaturas-config.edit', $colegiatura) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="{{ route('colegiaturas-config.destroy', $colegiatura) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta colegiatura del catálogo? Los alumnos vinculados pasarán a tener colegiatura sin categoría base, pero conservarán el costo actual.')">
                                    <i class="fas fa-trash"></i> Eliminar
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

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#colegiaturas-config-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": 3 }
            ]
        });
    });
</script>
@stop
