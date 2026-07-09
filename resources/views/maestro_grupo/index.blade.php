@extends('adminlte::page')

@section('title', 'Asignar Maestro de Planta')

@section('content_header')
    <h1 class="text-primary"><i class="fas fa-chalkboard-teacher mr-2"></i> Asignación de Maestro de Planta a Grupo</h1>
@stop

@section('content')
<div class="row">
    {{-- Formulario de Asignación --}}
    <div class="col-md-4">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-plus mr-1"></i> Asignar / Cambiar Maestro</h3>
            </div>
            <form action="{{ route('maestro_grupo.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="form-group">
                        <label for="grado_grupo_id">Grado y Grupo:</label>
                        <select name="grado_grupo_id" id="grado_grupo_id" class="form-control" required>
                            <option value="">Seleccione grupo...</option>
                            @foreach($gradoGrupos as $g)
                                <option value="{{ $g->id }}">
                                    Grado {{ $g->grado }} - Grupo "{{ $g->grupo }}"
                                    @if($g->maestro)
                                        (Actual: {{ $g->maestro->nombre }} {{ $g->maestro->apellido_paterno }})
                                    @else
                                        (Sin Maestro)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Seleccione el grupo al cual se le asignará el tutor principal.</small>
                    </div>

                    <div class="form-group">
                        <label for="profesor_id">Profesor (Maestro de Planta):</label>
                        <select name="profesor_id" id="profesor_id" class="form-control" required>
                            <option value="">Seleccione profesor...</option>
                            @foreach($profesores as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->apellido_paterno }} {{ $p->apellido_materno }} {{ $p->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Este docente figurará como titular del grupo en la boleta de calificaciones.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block shadow-sm">
                        <i class="fas fa-save mr-1"></i> Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla de Asignaciones Actuales --}}
    <div class="col-md-8">
        <div class="card card-outline card-info shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-list mr-1"></i> Listado de Grupos y Tutores</h3>
            </div>
            <div class="card-body p-0">
                @if(session('success'))
                    <div class="alert alert-success m-3 alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table id="maestro-grupo-table" class="table table-hover table-striped mb-0">
                        <thead class="bg-info text-white">
                            <tr>
                                <th style="width: 10%">ID</th>
                                <th style="width: 30%">Grado y Grupo</th>
                                <th>Maestro de Planta (Tutor)</th>
                                <th style="width: 15%" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gradoGrupos as $g)
                                <tr>
                                    <td><code>#{{ $g->id }}</code></td>
                                    <td>
                                        <span class="badge badge-secondary px-2 py-1 font-weight-bold" style="font-size: 0.95rem;">
                                            Grado {{ $g->grado }}° - "{{ $g->grupo }}"
                                        </span>
                                    </td>
                                    <td>
                                        @if($g->maestro)
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-tie text-muted mr-2" style="font-size: 1.1rem;"></i>
                                                <span class="font-weight-bold text-dark">
                                                    {{ $g->maestro->nombre }} {{ $g->maestro->apellido_paterno }} {{ $g->maestro->apellido_materno }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="badge badge-warning px-2 py-1">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Sin asignar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($g->maestro)
                                            <form action="{{ route('maestro_grupo.destroy', $g->id) }}" method="POST"
                                                  onsubmit="return confirm('¿Está seguro de remover a este maestro del grupo {{ $g->grado }}° \"{{ $g->grupo }}\"?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold shadow-sm" title="Desasignar Maestro">
                                                    <i class="fas fa-user-minus mr-1"></i> Quitar
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-light disabled" disabled>
                                                <i class="fas fa-minus text-muted"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#maestro-grupo-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": [3] }
            ],
            "order": [[1, 'asc']]
        });
    });
</script>
@stop
