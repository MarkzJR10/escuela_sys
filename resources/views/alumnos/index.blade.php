@extends('adminlte::page')

@section('title', 'Alumnos')

@section('content_header')
    <h1>Lista de Alumnos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('alumnos.create') }}" class="btn btn-primary">Nuevo Alumno</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="icon fas fa-ban"></i> Hubo un problema al procesar la acción.
                </div>
            @endif

            <table id="alumnos-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre Completo</th>
                        <th>CURP</th>
                        <th>Grado y Grupo</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alumnos as $alumno)
                    <tr>
                        <td><code>{{ $alumno->matricula }}</code></td>
                        <td>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                        <td>{{ $alumno->curp }}</td>
                        <td>{{ $alumno->gradoGrupo->grado ?? '' }} {{ $alumno->gradoGrupo->grupo ?? '' }}</td>
                        <td>
                            @php $st = strtolower($alumno->estatus ?? 'regular'); @endphp
                            @if($st === 'baja')
                                <span class="badge badge-danger">Baja</span>
                            @elseif($st === 'egresado')
                                <span class="badge badge-secondary">Egresado</span>
                            @else
                                <span class="badge badge-success">Regular</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('alumnos.edit', $alumno) }}" class="btn btn-warning btn-sm" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('alumnos.destroy', $alumno) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Seguro que deseas eliminar a este alumno?')">
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

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#alumnos-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": 5 } // Disable ordering on "Acciones" column
            ]
        });
    });
</script>
@stop
