@extends('adminlte::page')

@section('title', 'Captura de Calificaciones')

@section('content_header')
    <h1>Captura de Calificaciones por Trimestre</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros de Selección</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('calificaciones.captura') }}" method="GET" id="filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Grado y Grupo</label>
                            <select name="grado_grupo_id" class="form-control select2" required onchange="this.form.submit()">
                                <option value="">-- Seleccione --</option>
                                @foreach($gradoGrupos as $gg)
                                    <option value="{{ $gg->id }}" {{ request('grado_grupo_id') == $gg->id ? 'selected' : '' }}>
                                        {{ $gg->grado }} {{ $gg->grupo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Materia</label>
                            <select name="materia_id" class="form-control select2" required onchange="this.form.submit()">
                                <option value="">-- Seleccione --</option>
                                @foreach($materias as $materia)
                                    <option value="{{ $materia->id }}" {{ request('materia_id') == $materia->id ? 'selected' : '' }}>
                                        {{ $materia->nombre }} ({{ $materia->grado }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group">
                            <button type="submit" class="btn btn-info"><i class="fas fa-search"></i> Cargar Alumnos</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(count($alumnos) > 0)
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Listado de Alumnos - Captura de Calificaciones</h3>
            </div>
            <form action="{{ route('calificaciones.bulk_store') }}" method="POST">
                @csrf
                <input type="hidden" name="grado_grupo_id" value="{{ request('grado_grupo_id') }}">
                <input type="hidden" name="materia_id" value="{{ request('materia_id') }}">

                <div class="card-body p-0">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th style="width: 120px">T1</th>
                                <th style="width: 120px">T2</th>
                                <th style="width: 120px" title="T3">T3</th>
                                <th>Última Actualización</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $alumno)
                                @php
                                    $t1 = $alumno->calificaciones->where('trimestre', 1)->first();
                                    $t2 = $alumno->calificaciones->where('trimestre', 2)->first();
                                    $t3 = $alumno->calificaciones->where('trimestre', 3)->first();
                                    $ultima = $alumno->calificaciones->sortByDesc('updated_at')->first();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</strong><br>
                                        <small class="text-muted">Matrícula: {{ $alumno->matricula }}</small>
                                    </td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="10" 
                                               name="notas[{{ $alumno->id }}][1]" 
                                               class="form-control" 
                                               value="{{ old('notas.'.$alumno->id.'.1', $t1 ? $t1->puntaje : '') }}" 
                                               placeholder="0.0"
                                               {{ !($periodosControl[1] ?? false) ? 'disabled title=Periodo_Cerrado' : '' }}>
                                    </td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="10" 
                                               name="notas[{{ $alumno->id }}][2]" 
                                               class="form-control" 
                                               value="{{ old('notas.'.$alumno->id.'.2', $t2 ? $t2->puntaje : '') }}" 
                                               placeholder="0.0"
                                               {{ !($periodosControl[2] ?? false) ? 'disabled title=Periodo_Cerrado' : '' }}>
                                    </td>
                                    <td>
                                        <input type="number" step="0.1" min="0" max="10" 
                                               name="notas[{{ $alumno->id }}][3]" 
                                               class="form-control" 
                                               value="{{ old('notas.'.$alumno->id.'.3', $t3 ? $t3->puntaje : '') }}" 
                                               placeholder="0.0"
                                               {{ !($periodosControl[3] ?? false) ? 'disabled title=Periodo_Cerrado' : '' }}>
                                    </td>
                                    <td>
                                        {{ $ultima ? $ultima->updated_at->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success float-right">
                        <i class="fas fa-save"></i> Guardar Calificaciones
                    </button>
                    <p class="text-muted"><i class="fas fa-info-circle"></i> Puedes capturar uno o varios trimestres a la vez.</p>
                </div>
            </form>
        </div>
    @elseif(request()->filled(['grado_grupo_id', 'materia_id']))
        <div class="alert alert-warning">
            <i class="icon fas fa-exclamation-triangle"></i> No se encontraron alumnos en este grupo.
        </div>
    @endif
@stop

@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    });
</script>
@endpush
