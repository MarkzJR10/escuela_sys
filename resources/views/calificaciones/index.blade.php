@extends('adminlte::page')

@section('title', 'Calificaciones')

@section('content_header')
    <h1>{{ $isTeacher ? 'Captura de Calificaciones' : 'Gestión de Calificaciones' }}</h1>
@stop

@section('content')
    <div class="card card-outline {{ $isTeacher ? 'card-primary' : 'card-info' }}">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <h3 class="card-title font-weight-bold text-dark">
                        @if($isTeacher)
                            <i class="fas fa-chalkboard-teacher mr-1 text-primary"></i> Mis Grupos y Materias
                        @else
                            <i class="fas fa-filter mr-1 text-info"></i> Filtro de Calificaciones
                        @endif
                    </h3>
                </div>
                <div class="col-md-7">
                    @if($isTeacher)
                        {{-- Para el Maestro: 1 solo select con sus asignaciones (materia - gradogrupo) --}}
                        <form action="{{ route('calificaciones.index') }}" method="GET" class="form-inline float-right">
                            <label for="asignacion" class="mr-2 font-weight-bold text-dark">
                                <i class="fas fa-book-reader mr-1 text-primary"></i> Asignación:
                            </label>
                            <select name="asignacion" id="asignacion" class="form-control font-weight-bold" onchange="this.form.submit()" style="min-width: 300px;" {{ $asignacionesDocente->isEmpty() ? 'disabled' : '' }}>
                                @if($asignacionesDocente->isEmpty())
                                    <option value="">-- Sin asignaciones de materias/grupos --</option>
                                @else
                                    @foreach($asignacionesDocente as $asig)
                                        <option value="{{ $asig['key'] }}" {{ ($asignacionKey == $asig['key']) ? 'selected' : '' }}>
                                            {{ $asig['label'] }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </form>
                    @else
                        {{-- Para Administradores y Coordinadores: selects separados de Grupo y Materia --}}
                        <form action="{{ route('calificaciones.index') }}" method="GET" class="form-inline float-right">
                            <select name="grado_grupo_id" class="form-control mr-2">
                                <option value="">-- Grupo --</option>
                                @foreach($gradoGrupos as $gg)
                                    <option value="{{ $gg->id }}" {{ ($grado_grupo_id == $gg->id) ? 'selected' : '' }}>
                                        {{ $gg->grado }} "{{ $gg->grupo }}"
                                    </option>
                                @endforeach
                            </select>
                            <select name="materia_id" class="form-control mr-2">
                                <option value="">-- Materia --</option>
                                @foreach($materias as $m)
                                    <option value="{{ $m->id }}" {{ ($materia_id == $m->id) ? 'selected' : '' }}>
                                        {{ $m->nombre_con_grado ?? $m->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-default"><i class="fas fa-filter"></i> Filtrar</button>
                            @if(request()->anyFilled(['grado_grupo_id', 'materia_id', 'asignacion']))
                                <a href="{{ route('calificaciones.index') }}" class="btn btn-link">Limpiar</a>
                            @endif
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-ban"></i> {{ session('error') }}
                </div>
            @endif

            @if($isTeacher)
                @if($asignacionesDocente->isEmpty())
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Sin asignaciones activas</h5>
                        No tienes materias ni grupos asignados actualmente. Solicita al administrador que registre tus clases en <strong>Asignar Maestro-Materia</strong>.
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> Captura o actualiza las notas directamente en las casillas correspondientes y haz clic en <strong>Guardar Calificaciones</strong>.
                    </div>
                @endif
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i> <strong>Nota:</strong> Para capturar o editar calificaciones, seleccione un Grupo y una Materia en el filtro superior.
                </div>
            @endif

            @php
                $isFiltering = (!empty($materia_id) && !empty($grado_grupo_id)) || request()->filled('alumno_id');
                $canCapture = Auth::user()->hasAnyRole(['administrador', 'coordinador', 'profesor', 'maestro']);
            @endphp

            @if($isFiltering && $canCapture)
                <form action="{{ route('calificaciones.bulk_store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="grado_grupo_id" value="{{ $grado_grupo_id }}">
                    <input type="hidden" name="materia_id" value="{{ $materia_id }}">
                    @if($isTeacher && $asignacionKey)
                        <input type="hidden" name="asignacion" value="{{ $asignacionKey }}">
                    @endif
            @endif

            <table id="calificaciones-table" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Materia</th>
                        <th class="text-center" style="width: 110px">T1</th>
                        <th class="text-center" style="width: 110px">T2</th>
                        <th class="text-center" style="width: 110px">T3</th>
                        <th>Última Actualización</th>
                        @if(!$isFiltering) <th class="text-center" style="width: 100px">Acciones</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td class="align-middle">
                            <span class="font-weight-bold text-dark">
                                {{ $row['alumno']->nombre }} {{ $row['alumno']->apellido_paterno }} {{ $row['alumno']->apellido_materno }}
                            </span>
                            <br>
                            <small class="text-muted">
                                Matrícula: {{ $row['alumno']->matricula ?? 'S/N' }} | 
                                Grupo: {{ $row['alumno']->gradoGrupo->grado ?? '' }} "{{ $row['alumno']->gradoGrupo->grupo ?? '' }}"
                            </small>
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-light border px-2 py-1 font-weight-bold">
                                {{ $row['materia']->nombre }}
                            </span>
                        </td>
                        <td class="text-center align-middle">
                            @if($isFiltering && $canCapture)
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="notas[{{ $row['alumno']->id }}][1]" 
                                       class="form-control form-control-sm text-center font-weight-bold" 
                                       placeholder="-"
                                       value="{{ $row['t1'] ? $row['t1']->puntaje : '' }}"
                                       {{ !($periodosControl[1] ?? false) && !Auth::user()->hasAnyRole(['administrador', 'coordinador']) ? 'disabled title=Periodo_Cerrado' : '' }}>
                            @elseif($row['t1'])
                                <span class="badge {{ $row['t1']->puntaje >= 6 ? 'badge-success' : 'badge-danger' }} px-2 py-1 font-weight-bold" style="font-size: 0.95rem;">
                                    {{ number_format($row['t1']->puntaje, 1) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($isFiltering && $canCapture)
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="notas[{{ $row['alumno']->id }}][2]" 
                                       class="form-control form-control-sm text-center font-weight-bold" 
                                       placeholder="-"
                                       value="{{ $row['t2'] ? $row['t2']->puntaje : '' }}"
                                       {{ !($periodosControl[2] ?? false) && !Auth::user()->hasAnyRole(['administrador', 'coordinador']) ? 'disabled title=Periodo_Cerrado' : '' }}>
                            @elseif($row['t2'])
                                <span class="badge {{ $row['t2']->puntaje >= 6 ? 'badge-success' : 'badge-danger' }} px-2 py-1 font-weight-bold" style="font-size: 0.95rem;">
                                    {{ number_format($row['t2']->puntaje, 1) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            @if($isFiltering && $canCapture)
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="notas[{{ $row['alumno']->id }}][3]" 
                                       class="form-control form-control-sm text-center font-weight-bold" 
                                       placeholder="-"
                                       value="{{ $row['t3'] ? $row['t3']->puntaje : '' }}"
                                       {{ !($periodosControl[3] ?? false) && !Auth::user()->hasAnyRole(['administrador', 'coordinador']) ? 'disabled title=Periodo_Cerrado' : '' }}>
                            @elseif($row['t3'])
                                <span class="badge {{ $row['t3']->puntaje >= 6 ? 'badge-success' : 'badge-danger' }} px-2 py-1 font-weight-bold" style="font-size: 0.95rem;">
                                    {{ number_format($row['t3']->puntaje, 1) }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <small class="text-muted">
                                {{ $row['ultima'] ? $row['ultima']->format('d/m/Y H:i') : 'Sin registro' }}
                            </small>
                        </td>
                        @if(!$isFiltering)
                        <td class="text-center align-middle">
                            @hasanyrole('administrador|coordinador')
                                <a href="{{ route('calificaciones.index', ['alumno_id' => $row['alumno']->id, 'materia_id' => $row['materia']->id, 'grado_grupo_id' => $row['alumno']->grado_grupo_id]) }}" class="btn btn-primary btn-sm" title="Editar calificación">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endhasanyrole
                        </td>
                        @endif
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($isFiltering && $canCapture && count($data) > 0)
                <div class="mt-3">
                    <button type="submit" class="btn btn-success float-right font-weight-bold px-4 py-2 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Guardar Calificaciones
                    </button>
                    @if(!$isTeacher)
                        <a href="{{ route('calificaciones.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Quitar Filtros
                        </a>
                    @endif
                </div>
                </form>
            @endif
        </div>
    </div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#calificaciones-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "order": [[0, "asc"]],
            "pageLength": 25,
            "responsive": true
        });
    });
</script>
@stop
