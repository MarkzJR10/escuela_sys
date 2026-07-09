@extends('adminlte::page')

@section('title', 'Asignar Maestro a Materia')

@section('content_header')
    <h1>Asignación Maestro - Materia</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Nueva Asignación</h3>
            </div>
            <form action="{{ route('maestro_materia.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Profesor</label>
                        <select name="profesor_id" class="form-control" required>
                            <option value="">Seleccione profesor...</option>
                            @foreach($profesores as $p)
                                <option value="{{ $p->id }}">{{ $p->apellido_paterno }} {{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Materia</label>
                        <select name="materia_id" class="form-control" required>
                            <option value="">Seleccione materia...</option>
                            @foreach($materias as $m)
                                <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Grado/Grupo (Opcional)</label>
                        <select name="grado_grupo_id" class="form-control">
                            <option value="">Aplica a todos los grados...</option>
                            @foreach($gradoGrupos as $g)
                                <option value="{{ $g->id }}">{{ $g->grado }} "{{ $g->grupo }}"</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Deje en blanco si el maestro da esta materia a todos los grados.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Guardar Asignación</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Asignaciones Actuales</h3>
            </div>
            <div class="card-body p-0">
                <table id="maestro-materia-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Profesor</th>
                            <th>Materia</th>
                            <th>Grado Específico</th>
                            <th width="100px">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $a)
                            <tr>
                                <td>{{ $a->profesor_apellido }} {{ $a->profesor_nombre }}</td>
                                <td>{{ $a->materia_nombre }}</td>
                                <td>
                                    @if($a->grado)
                                        {{ $a->grado }} "{{ $a->grupo }}"
                                    @else
                                        <span class="badge badge-secondary">General</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('maestro_materia.destroy', $a->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar esta asignación?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No hay asignaciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#maestro-materia-table').DataTable({
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
