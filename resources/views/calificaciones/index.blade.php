@extends('adminlte::page')

@section('title', 'Calificaciones')

@section('content_header')
    <h1>Gestión de Calificaciones</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-12">
                    <form action="{{ route('calificaciones.index') }}" method="GET" class="form-inline float-right">
                        <select name="grado_grupo_id" class="form-control mr-2">
                            <option value="">-- Grupo --</option>
                            @foreach($gradoGrupos as $gg)
                                <option value="{{ $gg->id }}" {{ request('grado_grupo_id') == $gg->id ? 'selected' : '' }}>
                                    {{ $gg->grado }} {{ $gg->grupo }}
                                </option>
                            @endforeach
                        </select>
                        <select name="materia_id" class="form-control mr-2">
                            <option value="">-- Materia --</option>
                            @foreach($materias as $m)
                                <option value="{{ $m->id }}" {{ request('materia_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-default">Filtrar</button>
                        @if(request()->anyFilled(['grado_grupo_id', 'materia_id']))
                            <a href="{{ route('calificaciones.index') }}" class="btn btn-link">Limpiar</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> <strong>Nota:</strong> Para capturar o editar calificaciones, es necesario realizar el filtro por Grado/Grupo y Materia, o utilizar el botón de edición directa.
            </div>

            @php
                $isFiltering = request()->filled(['grado_grupo_id', 'materia_id']) || request()->filled('alumno_id');
                $canCapture = Auth::user()->hasAnyRole(['administrador', 'profesor']);
            @endphp

            @if($isFiltering && $canCapture)
                <form action="{{ route('calificaciones.bulk_store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="grado_grupo_id" value="{{ request('grado_grupo_id') }}">
                    <input type="hidden" name="materia_id" value="{{ request('materia_id') }}">
            @endif

            <table id="calificaciones-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Alumno</th>
                        <th>Materia</th>
                        <th class="text-center" style="width: 100px">T1</th>
                        <th class="text-center" style="width: 100px">T2</th>
                        <th class="text-center" style="width: 100px">T3</th>
                        <th>Actualización</th>
                        @if(!$isFiltering) <th>Acciones</th> @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $row)
                    <tr>
                        <td>
                            <strong>{{ $row['alumno']->nombre }} {{ $row['alumno']->apellido_paterno }}</strong><br>
                            <small class="text-muted">{{ $row['alumno']->gradoGrupo->grado }} {{ $row['alumno']->gradoGrupo->grupo }}</small>
                        </td>
                        <td>{{ $row['materia']->nombre }}</td>
                        <td class="text-center">
                            @if($isFiltering && $canCapture)
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="notas[{{ $row['alumno']->id }}][1]" 
                                       class="form-control form-control-sm text-center" 
                                       value="{{ $row['t1'] ? $row['t1']->puntaje : '' }}"
                                       {{ !($periodosControl[1] ?? false) && !Auth::user()->hasRole('administrador') ? 'disabled title=Periodo_Cerrado' : '' }}>
                            @elseif($row['t1'])
                                <span class="badge {{ $row['t1']->puntaje >= 6 ? 'badge-success' : 'badge-danger' }}">
                                    {{ number_format($row['t1']->puntaje, 1) }}
                                </span>
                            @else - @endif
                        </td>
                        <td class="text-center">
                            @if($isFiltering && $canCapture)
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="notas[{{ $row['alumno']->id }}][2]" 
                                       class="form-control form-control-sm text-center" 
                                       value="{{ $row['t2'] ? $row['t2']->puntaje : '' }}"
                                       {{ !($periodosControl[2] ?? false) && !Auth::user()->hasRole('administrador') ? 'disabled title=Periodo_Cerrado' : '' }}>
                            @elseif($row['t2'])
                                <span class="badge {{ $row['t2']->puntaje >= 6 ? 'badge-success' : 'badge-danger' }}">
                                    {{ number_format($row['t2']->puntaje, 1) }}
                                </span>
                            @else - @endif
                        </td>
                        <td class="text-center">
                            @if($isFiltering && $canCapture)
                                <input type="number" step="0.1" min="0" max="10" 
                                       name="notas[{{ $row['alumno']->id }}][3]" 
                                       class="form-control form-control-sm text-center" 
                                       value="{{ $row['t3'] ? $row['t3']->puntaje : '' }}"
                                       {{ !($periodosControl[3] ?? false) && !Auth::user()->hasRole('administrador') ? 'disabled title=Periodo_Cerrado' : '' }}>
                            @elseif($row['t3'])
                                <span class="badge {{ $row['t3']->puntaje >= 6 ? 'badge-success' : 'badge-danger' }}">
                                    {{ number_format($row['t3']->puntaje, 1) }}
                                </span>
                            @else - @endif
                        </td>
                        <td>{{ $row['ultima'] ? $row['ultima']->format('d/m/Y H:i') : 'N/A' }}</td>
                        @if(!$isFiltering)
                        <td>
                            @role('administrador')
                                <a href="{{ route('calificaciones.index', ['alumno_id' => $row['alumno']->id, 'materia_id' => $row['materia']->id, 'grado_grupo_id' => $row['alumno']->grado_grupo_id]) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            @endrole
                            
                            @role('coordinador|profesor')
                                <div class="btn-group">
                                    <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown">
                                        Detalle
                                    </button>
                                    <div class="dropdown-menu">
                                        @if($row['t1'] && ($periodosControl[1] ?? false)) <a class="dropdown-item" href="{{ route('calificaciones.edit', $row['t1']) }}">T1</a> @endif
                                        @if($row['t2'] && ($periodosControl[2] ?? false)) <a class="dropdown-item" href="{{ route('calificaciones.edit', $row['t2']) }}">T2</a> @endif
                                        @if($row['t3'] && ($periodosControl[3] ?? false)) <a class="dropdown-item" href="{{ route('calificaciones.edit', $row['t3']) }}">T3</a> @endif
                                    </div>
                                </div>
                            @endrole
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isFiltering ? 6 : 7 }}" class="text-center">No hay alumnos o calificaciones registradas con estos filtros.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($isFiltering && $canCapture)
                <div class="mt-3">
                    <button type="submit" class="btn btn-success float-right">
                        <i class="fas fa-save"></i> Guardar Calificaciones
                    </button>
                    <a href="{{ route('calificaciones.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Regresar a Lista General
                    </a>
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
            "responsive": true,
        });
    });
</script>
@stop
