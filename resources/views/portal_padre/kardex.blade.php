@extends('adminlte::page')

@section('title', 'Historial Académico (Kardex)')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-graduation-cap text-purple mr-2"></i> Historial Académico (Kardex)</h1>
        <div>
            @if(\App\Models\Configuracion::get('portal_padre_ver_boleta', '1') == '1')
                <a href="{{ route('portal_padre.boleta', $alumno->id) }}" class="btn btn-outline-success mr-2">
                    <i class="fas fa-file-invoice mr-1"></i> Ver Boleta Actual
                </a>
            @endif
            <a href="{{ route('portal_padre.kardex_pdf', $alumno->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Descargar Kardex PDF
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-11 mx-auto">
        <!-- Resumen del Alumno -->
        <div class="card card-outline card-purple shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        @if($alumno->fotografia)
                            <img class="img-circle elevation-2" src="{{ asset('storage/'.$alumno->fotografia) }}" alt="Avatar" style="width: 75px; height: 75px; object-fit: cover;">
                        @else
                            <div class="bg-purple text-white rounded-circle d-flex align-items-center justify-content-center elevation-2" style="width: 75px; height: 75px; font-size: 2rem;">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col">
                        <h3 class="mb-1 font-weight-bold text-dark">{{ $alumno->nombre_completo }}</h3>
                        <div class="text-muted d-flex flex-wrap">
                            <span class="mr-3"><i class="fas fa-id-card text-muted mr-1"></i> Matrícula: <strong>{{ $alumno->matricula }}</strong></span>
                            @if($alumno->curp)
                                <span class="mr-3"><i class="fas fa-fingerprint text-muted mr-1"></i> CURP: <strong>{{ $alumno->curp }}</strong></span>
                            @endif
                            <span class="mr-3"><i class="fas fa-school text-muted mr-1"></i> Grado Actual: <strong>{{ $alumno->gradoGrupo ? $alumno->gradoGrupo->grado . ' "' . $alumno->gradoGrupo->grupo . '"' : 'Sin asignar' }}</strong></span>
                        </div>
                    </div>
                    <div class="col-auto text-right border-left pl-4">
                        <small class="text-muted text-uppercase font-weight-bold d-block">Promedio General Acumulado</small>
                        @if(!is_null($kardexData['promedio_general_acumulado']))
                            <div class="display-4 font-weight-bold {{ $kardexData['promedio_general_acumulado'] >= 8.0 ? 'text-success' : ($kardexData['promedio_general_acumulado'] >= 6.0 ? 'text-primary' : 'text-danger') }}" style="font-size: 2.5rem; line-height: 1.1;">
                                {{ number_format($kardexData['promedio_general_acumulado'], 1) }}
                            </div>
                            <small class="text-muted">De {{ $kardexData['total_materias_cursadas'] }} materias cursadas</small>
                        @else
                            <span class="text-muted font-italic">En proceso</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Listado de Calificaciones por Grado -->
        @if(count($kardexData['grados']) > 0)
            @foreach($kardexData['grados'] as $gradoInfo)
                <div class="card card-outline card-secondary shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h4 class="card-title font-weight-bold text-dark mb-0">
                            <i class="fas fa-layer-group text-primary mr-2"></i> {{ $gradoInfo['grado_label'] }}
                            @if(!empty($gradoInfo['ciclo']))
                                <small class="text-muted font-weight-normal ml-2">({{ $gradoInfo['ciclo'] }})</small>
                            @endif
                        </h4>
                        <div class="card-tools d-flex align-items-center">
                            <span class="mr-3 text-muted">
                                Promedio del Grado: 
                                <strong class="badge badge-pill {{ (!is_null($gradoInfo['promedio_grado']) && $gradoInfo['promedio_grado'] >= 8.0) ? 'badge-success' : ((!is_null($gradoInfo['promedio_grado']) && $gradoInfo['promedio_grado'] >= 6.0) ? 'badge-primary' : 'badge-danger') }} px-3 py-1" style="font-size: 0.95rem;">
                                    {{ !is_null($gradoInfo['promedio_grado']) ? number_format($gradoInfo['promedio_grado'], 1) : '-' }}
                                </strong>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 text-center">
                                <thead class="bg-light text-muted">
                                    <tr>
                                        <th class="text-left pl-4 py-2" style="width: 40%;">Materia / Asignatura</th>
                                        <th class="py-2" style="width: 15%;">1° Trimestre</th>
                                        <th class="py-2" style="width: 15%;">2° Trimestre</th>
                                        <th class="py-2" style="width: 15%;">3° Trimestre</th>
                                        <th class="py-2 bg-light font-weight-bold" style="width: 15%;">Promedio Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($gradoInfo['materias'] as $m)
                                        <tr>
                                            <td class="text-left pl-4 font-weight-bold text-dark align-middle">
                                                <i class="fas fa-book text-muted mr-2"></i> {{ $m['materia'] }}
                                            </td>
                                            <td class="align-middle">
                                                @if(!is_null($m['p1']))
                                                    <span class="font-weight-bold {{ $m['p1'] < 6.0 ? 'text-danger' : 'text-dark' }}">
                                                        {{ number_format($m['p1'], 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if(!is_null($m['p2']))
                                                    <span class="font-weight-bold {{ $m['p2'] < 6.0 ? 'text-danger' : 'text-dark' }}">
                                                        {{ number_format($m['p2'], 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle">
                                                @if(!is_null($m['p3']))
                                                    <span class="font-weight-bold {{ $m['p3'] < 6.0 ? 'text-danger' : 'text-dark' }}">
                                                        {{ number_format($m['p3'], 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle bg-light">
                                                @if(!is_null($m['promedio']))
                                                    <span class="badge {{ $m['promedio'] >= 8.0 ? 'badge-success' : ($m['promedio'] >= 6.0 ? 'badge-primary' : 'badge-danger') }} px-2 py-1" style="font-size: 0.95rem;">
                                                        {{ number_format($m['promedio'], 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted font-italic">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-3 text-muted">No hay materias registradas para este grado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td class="text-left pl-4 text-uppercase">Resumen de {{ $gradoInfo['grado_label'] }}</td>
                                        <td colspan="3" class="text-right text-muted pr-3 font-weight-normal">
                                            Aprobadas: <span class="badge badge-success">{{ $gradoInfo['materias_aprobadas'] }}</span>
                                            @if($gradoInfo['materias_reprobadas'] > 0)
                                                | Reprobadas: <span class="badge badge-danger">{{ $gradoInfo['materias_reprobadas'] }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center py-2" style="font-size: 1.05rem;">
                                            @if(!is_null($gradoInfo['promedio_grado']))
                                                <span class="badge badge-pill {{ $gradoInfo['promedio_grado'] >= 8.0 ? 'badge-success' : ($gradoInfo['promedio_grado'] >= 6.0 ? 'badge-primary' : 'badge-danger') }} px-3 py-1">
                                                    {{ number_format($gradoInfo['promedio_grado'], 1) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="card shadow-sm">
                <div class="card-body p-5 text-center text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
                    <h4>Aún no hay calificaciones históricas registradas</h4>
                    <p class="mb-0">El historial del alumno se irá formando conforme concluyan los periodos y grados escolares.</p>
                </div>
            </div>
        @endif

        <div class="text-center mt-4 mb-5">
            <a href="{{ route('portal_padre.dashboard') }}" class="btn btn-default mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Mis Hijos
            </a>
            <a href="{{ route('portal_padre.kardex_pdf', $alumno->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Descargar Historial Completo (PDF)
            </a>
        </div>
    </div>
</div>
@stop
