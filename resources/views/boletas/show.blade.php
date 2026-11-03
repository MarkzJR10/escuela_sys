@extends('adminlte::page')

@section('title', 'Boleta de Calificaciones - ' . $alumno->matricula)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice text-success mr-2"></i> Boleta Oficial de Calificaciones</h1>
        <div>
            <a href="{{ route('boletas.kardex', $alumno->id) }}" class="btn btn-outline-primary mr-2">
                <i class="fas fa-graduation-cap mr-1"></i> Histórico (Kardex)
            </a>
            <a href="{{ route('boletas.pdf', $alumno->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Ver / Descargar Boleta PDF Oficial
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Encabezado estilo Boleta Oficial -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="{{ asset('img/logoh.jpeg') }}" alt="Colegio Iberoamericano de Cadereyta" style="max-height: 65px; max-width: 100%;">
                    <h4 class="font-weight-bold mb-0 mt-2">{{ $boletaData['level_title'] ?? 'PRIMARY' }}</h4>
                    <div class="text-muted">SCHOOL YEAR {{ $boletaData['ciclo'] }}</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0" style="font-size: 0.92rem;">
                        <tbody>
                            <tr>
                                <th class="bg-light" style="width: 18%;">STUDENT'S NAME:</th>
                                <td style="width: 47%;">{{ $boletaData['nombre_oficial'] ?? $alumno->nombre_completo }}</td>
                                <th class="bg-light" style="width: 15%;">ID NUMBER:</th>
                                <td colspan="3" style="width: 20%;"><code>{{ $alumno->matricula }}</code></td>
                            </tr>
                            <tr>
                                <th class="bg-light">HOMEROOM TEACHER:</th>
                                <td>{{ $boletaData['maestro_oficial'] ?? ($boletaData['maestro'] ?: 'SIN ASIGNAR') }}</td>
                                <th class="bg-light">GRADE:</th>
                                <td class="text-center font-weight-bold" style="width: 8%;">{{ $boletaData['grado_grupo_label'] ?? ($boletaData['grado'].$boletaData['grupo']) }}</td>
                                <th class="bg-light" style="width: 12%;">LIST NUMBER:</th>
                                <td class="text-center" style="width: 8%;">{{ $boletaData['list_number'] ?? 1 }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Español: Campo Formativo y Asignaturas -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                    <i class="fas fa-book text-primary mr-2"></i> Asignaturas Español (Campos Formativos)
                </h3>
                <div class="card-tools">
                    <a href="{{ route('calificaciones.captura') }}?grado_grupo_id={{ $alumno->grado_grupo_id }}" class="btn btn-xs btn-outline-warning">
                        <i class="fas fa-edit mr-1"></i> Ir a Captura de Calificaciones
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="align-middle" style="min-width: 120px;">ASIGNATURA</th>
                                @foreach($boletaData['espanol_materias'] ?? [] as $mat)
                                    <th class="align-middle">{{ $mat['materia'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([1 => 'p1', 2 => 'p2', 3 => 'p3'] as $tNum => $pField)
                            <tr>
                                <th class="bg-light align-middle">TRIMESTRE {{ $tNum }}</th>
                                @foreach($boletaData['espanol_materias'] ?? [] as $mat)
                                    <td class="align-middle font-weight-bold {{ (!is_null($mat[$pField]) && $mat[$pField] < 7.0) ? 'text-danger' : '' }}">
                                        {{ (!is_null($mat[$pField]) && $mat[$pField] > 0) ? number_format($mat[$pField], 1) : '' }}
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- English Subjects + Extracurricular -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-2">
                        <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                            <i class="fas fa-globe-americas text-info mr-2"></i> English Subjects
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="align-middle" style="min-width: 115px;">SUBJECTS</th>
                                        @foreach($boletaData['english_subjects'] ?? [] as $eng)
                                            <th class="align-middle">{{ $eng['materia'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach([1 => 'p1', 2 => 'p2', 3 => 'p3'] as $tNum => $pField)
                                    <tr>
                                        <th class="bg-light align-middle">TRIMESTER {{ $tNum }}</th>
                                        @foreach($boletaData['english_subjects'] ?? [] as $eng)
                                            <td class="align-middle font-weight-bold {{ (!is_null($eng[$pField]) && $eng[$pField] < 7.0) ? 'text-danger' : '' }}">
                                                {{ (!is_null($eng[$pField]) && $eng[$pField] > 0) ? number_format($eng[$pField], 1) : '' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-2">
                        <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                            <i class="fas fa-futbol text-success mr-2"></i> Extracurricular
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="align-middle">CLASSES</th>
                                        @foreach($boletaData['extracurricular'] ?? [] as $ext)
                                            <th class="align-middle">{{ $ext['materia'] }}</th>
                                        @endforeach
                                        <th class="align-middle text-left">A = EXCELLENT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $scales = [1 => 'B = SATISFACTORY', 2 => 'C = AVERAGE', 3 => 'D = UNSATISFACTORY'];
                                    @endphp
                                    @foreach([1, 2, 3] as $tNum)
                                    <tr>
                                        <th class="bg-light align-middle">TRIMESTER {{ $tNum }}</th>
                                        @foreach($boletaData['extracurricular'] ?? [] as $ext)
                                            <td class="align-middle font-weight-bold">{{ $ext['t'.$tNum] ?? '' }}</td>
                                        @endforeach
                                        <td class="align-middle text-left font-weight-bold small">{{ $scales[$tNum] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Characteristics of a Successful Learner + Overall Average -->
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-2">
                        <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                            <i class="fas fa-user-check text-warning mr-2"></i> Characteristics of a Successful Learner
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th>RESPONSIBLE LEARNER</th>
                                        <th>PERSONAL HABITS</th>
                                        <th>CLASSROOM HABITS</th>
                                        <th>CONDUCT</th>
                                        <th>DAYS ABSENT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $personal = $boletaData['learner_characteristics']['PERSONAL HABITS'] ?? ['p1'=>null,'p2'=>null,'p3'=>null];
                                        $classroom = $boletaData['learner_characteristics']['CLASSROOM HABITS'] ?? ['p1'=>null,'p2'=>null,'p3'=>null];
                                        $conduct = $boletaData['learner_characteristics']['CONDUCT'] ?? ['p1'=>null,'p2'=>null,'p3'=>null];
                                        $days = $boletaData['days_absent'] ?? ['t1'=>'-','t2'=>'-','t3'=>'-'];
                                    @endphp
                                    @foreach([1 => 'p1', 2 => 'p2', 3 => 'p3'] as $tNum => $pField)
                                    <tr>
                                        <th class="bg-light">TRIMESTER {{ $tNum }}</th>
                                        <td class="font-weight-bold">{{ (!is_null($personal[$pField]) && $personal[$pField] > 0) ? number_format($personal[$pField], 1) : '' }}</td>
                                        <td class="font-weight-bold">{{ (!is_null($classroom[$pField]) && $classroom[$pField] > 0) ? number_format($classroom[$pField], 1) : '' }}</td>
                                        <td class="font-weight-bold">{{ (!is_null($conduct[$pField]) && $conduct[$pField] > 0) ? number_format($conduct[$pField], 1) : '' }}</td>
                                        <td>{{ $days['t'.$tNum] ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-2">
                        <h3 class="card-title font-weight-bold text-dark mb-0" style="font-size: 1rem;">
                            <i class="fas fa-award text-success mr-2"></i> Overall Average & Final
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm text-center mb-0" style="font-size: 0.9rem;">
                            <thead class="bg-light">
                                <tr>
                                    <th>TRIMESTER</th>
                                    <th>OVERALL AVERAGE</th>
                                    <th>FINAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="bg-light">TRIMESTER 1</th>
                                    <td class="font-weight-bold">{{ $boletaData['promedio_t1'] ?: '-' }}</td>
                                    <td rowspan="3" class="align-middle bg-light font-weight-bold" style="font-size: 1.25rem;">
                                        <span class="badge badge-success px-3 py-2">
                                            {{ $boletaData['promedio_final_oficial'] ?: ($boletaData['promedio_general'] ? number_format($boletaData['promedio_general'], 2) : '-') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">TRIMESTER 2</th>
                                    <td class="font-weight-bold">{{ $boletaData['promedio_t2'] ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">TRIMESTER 3</th>
                                    <td class="font-weight-bold">{{ $boletaData['promedio_t3'] ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <a href="{{ route('boletas.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                </a>
                <div>
                    <a href="{{ route('boletas.kardex', $alumno->id) }}" class="btn btn-primary mr-2">
                        <i class="fas fa-graduation-cap mr-1"></i> Histórico (Kardex)
                    </a>
                    <a href="{{ route('boletas.pdf', $alumno->id) }}" class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf mr-1"></i> Descargar PDF Oficial
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
