@extends('adminlte::page')

@section('title', 'Migrar Grados')

@section('content_header')
    <h1>Migración de Grados (Cambio de Ciclo)</h1>
@stop

@section('content')
<div class="row">
    <!-- Migración Masiva -->
    <div class="col-md-5">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Migración Masiva</h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Mueve a todos los alumnos de un grado a otro. (Ej. 1ro A pasa a 2do A).</p>
                <form action="{{ route('migrar_grados.masivo') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Grado Origen</label>
                        <select name="grado_grupo_origen_id" class="form-control" required>
                            <option value="">Seleccione origen...</option>
                            @foreach($gradosDestino as $g)
                                <option value="{{ $g->id }}">{{ $g->grado }} "{{ $g->grupo }}"</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group text-center">
                        <i class="fas fa-arrow-down fa-2x text-muted"></i>
                    </div>
                    <div class="form-group">
                        <label>Grado Destino</label>
                        <select name="grado_grupo_destino_id" class="form-control" required>
                            <option value="">Seleccione destino...</option>
                            @foreach($gradosDestino as $g)
                                <option value="{{ $g->id }}">{{ $g->grado }} "{{ $g->grupo }}"</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('¿Está seguro de migrar todos los alumnos? Esta acción afectará a todo el grupo.')">
                        <i class="fas fa-exchange-alt"></i> Procesar Migración Masiva
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Gestión Individual -->
    <div class="col-md-7">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Gestión Individual</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('migrar_grados.index') }}" method="GET" class="mb-3 form-inline">
                    <select name="grado_grupo_id" class="form-control mr-2" required>
                        <option value="">Ver alumnos de...</option>
                        @foreach($gradoGrupos as $g)
                            <option value="{{ $g->id }}" {{ request('grado_grupo_id') == $g->id ? 'selected' : '' }}>
                                {{ $g->grado }} "{{ $g->grupo }}"
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="{{ route('migrar_grados.inactivos') }}" class="btn btn-secondary ml-auto">Ver Inactivos (Bajas)</a>
                </form>

                @if($alumnos->count() > 0)
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>Grado Actual</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $alumno)
                            <tr>
                                <td class="align-middle">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</td>
                                <td class="align-middle">{{ $alumno->gradoGrupo->grado ?? '' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"</td>
                                <td>
                                    <!-- Migración Individual -->
                                    <form action="{{ route('migrar_grados.alumno') }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                                        <div class="input-group input-group-sm" style="width: 200px;">
                                            <select name="nuevo_grado_grupo_id" class="form-control" required>
                                                <option value="">Cambiar a...</option>
                                                @foreach($gradosDestino as $g)
                                                    <option value="{{ $g->id }}">{{ $g->grado }}{{ $g->grupo }}</option>
                                                @endforeach
                                            </select>
                                            <span class="input-group-append">
                                                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i></button>
                                            </span>
                                        </div>
                                    </form>
                                    
                                    <!-- Dar de baja -->
                                    <form action="{{ route('migrar_grados.dar_baja') }}" method="POST" class="d-inline-block ml-1">
                                        @csrf
                                        <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Dar de baja a este alumno?')" title="Suspender/Dar Baja">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
