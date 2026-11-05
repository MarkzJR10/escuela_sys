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
            @php
                $selectedGG = $gradoGrupos->firstWhere('id', request('grado_grupo_id'));
                $gradoActivo = $selectedGG ? $selectedGG->grado : null;
            @endphp
            <form action="{{ route('calificaciones.captura') }}" method="GET" id="filter-form">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Grado y Grupo</label>
                            <select name="grado_grupo_id" id="captura-grado-grupo" class="form-control select2" required>
                                <option value="" data-grado="">-- Seleccione --</option>
                                @foreach($gradoGrupos as $gg)
                                    <option value="{{ $gg->id }}" data-grado="{{ $gg->grado }}" {{ request('grado_grupo_id') == $gg->id ? 'selected' : '' }}>
                                        {{ $gg->grado }} {{ $gg->grupo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Materia</label>
                            <select name="materia_id" id="captura-materia" class="form-control select2" required onchange="this.form.submit()">
                                <option value="">-- Seleccione --</option>
                                @foreach($materias as $materia)
                                    @if(!$gradoActivo || empty($materia->grado) || $materia->grado == $gradoActivo)
                                        <option value="{{ $materia->id }}" data-grado="{{ $materia->grado }}" {{ request('materia_id') == $materia->id ? 'selected' : '' }}>
                                            {{ $materia->nombre }} ({{ $materia->grado }})
                                        </option>
                                    @endif
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
        @php
            $activeMateria = $selectedMateria ?? $materias->firstWhere('id', request('materia_id'));
            $isExtra = $activeMateria && $activeMateria->is_extracurricular;
        @endphp
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    Listado de Alumnos - Captura de Calificaciones
                    @if($activeMateria)
                        ({{ $activeMateria->nombre }})
                    @endif
                </h3>
                @if($isExtra)
                    <span class="badge badge-warning px-2 py-1">
                        Escala Extracurricular: A = Excellent | B = Satisfactory | C = Average | D = Unsatisfactory
                    </span>
                @endif
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
                                <th style="width: 150px">T1</th>
                                <th style="width: 150px">T2</th>
                                <th style="width: 150px" title="T3">T3</th>
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
                                    $trimMap = [1 => $t1, 2 => $t2, 3 => $t3];
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</strong><br>
                                        <small class="text-muted">Matrícula: {{ $alumno->matricula }}</small>
                                    </td>
                                    @foreach([1, 2, 3] as $trimNum)
                                        @php $tObj = $trimMap[$trimNum]; @endphp
                                        <td>
                                            @if($isExtra)
                                                @php
                                                    $valLetra = old("notas.{$alumno->id}.{$trimNum}", $tObj ? $tObj->letra_extracurricular : '');
                                                @endphp
                                                <select name="notas[{{ $alumno->id }}][{{ $trimNum }}]"
                                                        class="form-control font-weight-bold text-center"
                                                        {{ !($periodosControl[$trimNum] ?? false) && !Auth::user()->hasAnyRole(['administrador', 'coordinador']) ? 'disabled title=Periodo_Cerrado' : '' }}>
                                                    <option value="">-- Seleccione --</option>
                                                    <option value="A" {{ $valLetra === 'A' ? 'selected' : '' }}>A - Excellent</option>
                                                    <option value="B" {{ $valLetra === 'B' ? 'selected' : '' }}>B - Satisfactory</option>
                                                    <option value="C" {{ $valLetra === 'C' ? 'selected' : '' }}>C - Average</option>
                                                    <option value="D" {{ $valLetra === 'D' ? 'selected' : '' }}>D - Unsatisfactory</option>
                                                </select>
                                            @else
                                                <input type="number" step="0.1" min="0" max="10" 
                                                       name="notas[{{ $alumno->id }}][{{ $trimNum }}]" 
                                                       class="form-control" 
                                                       value="{{ old("notas.{$alumno->id}.{$trimNum}", $tObj ? $tObj->puntaje : '') }}" 
                                                       placeholder="0.0"
                                                       {{ !($periodosControl[$trimNum] ?? false) && !Auth::user()->hasAnyRole(['administrador', 'coordinador']) ? 'disabled title=Periodo_Cerrado' : '' }}>
                                            @endif
                                        </td>
                                    @endforeach
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

        @php
            $materiasCapturaJson = $materias->map(function($m) {
                return [
                    'id' => $m->id,
                    'grado' => (string) ($m->grado ?? ''),
                    'label' => $m->nombre . ' (' . $m->grado . ')',
                ];
            })->values();
        @endphp
        var allMaterias = @json($materiasCapturaJson);

        $('#captura-grado-grupo').on('change', function() {
            var selectedGrado = String($(this).find('option:selected').data('grado') || '');
            var $materiaSelect = $('#captura-materia');

            $materiaSelect.empty();
            $materiaSelect.append('<option value="">-- Seleccione --</option>');

            allMaterias.forEach(function(m) {
                if (!selectedGrado || !m.grado || m.grado === selectedGrado) {
                    $materiaSelect.append(
                        $('<option>', {
                            value: m.id,
                            text: m.label,
                            'data-grado': m.grado
                        })
                    );
                }
            });

            $materiaSelect.trigger('change.select2');
        });
    });
</script>
@endpush
