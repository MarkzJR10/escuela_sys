@extends('adminlte::page')

@section('title', 'Boleta de Calificaciones - ' . $alumno->matricula)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-invoice text-success mr-2"></i> Boleta de Calificaciones</h1>
        <div>
            <a href="{{ route('boletas.kardex', $alumno->id) }}" class="btn btn-outline-primary mr-2">
                <i class="fas fa-graduation-cap mr-1"></i> Histórico (Kardex)
            </a>
            <a href="{{ route('boletas.pdf', $alumno->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Descargar Boleta PDF
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-11 mx-auto">
        <!-- Ficha del Alumno -->
        <div class="card card-outline card-success shadow-sm mb-4">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-user-graduate text-success mr-2"></i> {{ $alumno->nombre_completo }}
                </h3>
                <div class="card-tools">
                    <span class="badge badge-success px-3 py-2 font-weight-normal" style="font-size: 0.95rem;">
                        Ciclo Escolar: {{ $boletaData['ciclo'] }}
                    </span>
                </div>
            </div>
            <div class="card-body py-3">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <small class="text-muted d-block">Matrícula Escolar</small>
                        <strong class="text-dark"><code>{{ $alumno->matricula }}</code></strong>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <small class="text-muted d-block">Grado y Grupo Actual</small>
                        <strong class="text-dark">{{ $boletaData['grado'] }} "{{ $boletaData['grupo'] }}"</strong>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <small class="text-muted d-block">Profesor(a) de Planta</small>
                        <strong class="text-dark">{{ $boletaData['maestro'] ?: 'Sin asignar' }}</strong>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <small class="text-muted d-block">Promedio General</small>
                        @if(!is_null($boletaData['promedio_general']))
                            <span class="badge badge-pill {{ $boletaData['promedio_general'] >= 8.0 ? 'badge-success' : ($boletaData['promedio_general'] >= 6.0 ? 'badge-info' : 'badge-danger') }} px-3 py-1 font-weight-bold" style="font-size: 1rem;">
                                {{ number_format($boletaData['promedio_general'], 1) }}
                            </span>
                        @else
                            <span class="text-muted font-italic">En proceso</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Calificaciones -->
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-table text-info mr-2"></i> Calificaciones por Trimestre
                </h3>
                <div class="card-tools">
                    <a href="{{ route('calificaciones.captura') }}?grado_grupo_id={{ $alumno->grado_grupo_id }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit mr-1"></i> Ir a Captura de Calificaciones
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if(count($boletaData['materias']) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-left pl-4 py-3" style="width: 35%;">Asignatura</th>
                                    <th class="py-3" style="width: 15%;">1° Trimestre</th>
                                    <th class="py-3" style="width: 15%;">2° Trimestre</th>
                                    <th class="py-3" style="width: 15%;">3° Trimestre</th>
                                    <th class="py-3 bg-secondary text-white font-weight-bold" style="width: 20%;">Promedio Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boletaData['materias'] as $item)
                                <tr>
                                    <td class="text-left pl-4 font-weight-bold text-dark align-middle">
                                        <i class="fas fa-book-open text-muted mr-2"></i> {{ $item['materia'] }}
                                    </td>
                                    <td class="align-middle">
                                        @if(!is_null($item['p1']))
                                            <span class="font-weight-bold {{ $item['p1'] < 6.0 ? 'text-danger' : 'text-dark' }}">
                                                {{ number_format($item['p1'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if(!is_null($item['p2']))
                                            <span class="font-weight-bold {{ $item['p2'] < 6.0 ? 'text-danger' : 'text-dark' }}">
                                                {{ number_format($item['p2'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if(!is_null($item['p3']))
                                            <span class="font-weight-bold {{ $item['p3'] < 6.0 ? 'text-danger' : 'text-dark' }}">
                                                {{ number_format($item['p3'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="align-middle bg-light">
                                        @if(!is_null($item['p_final']))
                                            <span class="badge {{ $item['p_final'] >= 8.0 ? 'badge-success' : ($item['p_final'] >= 6.0 ? 'badge-primary' : 'badge-danger') }} px-2 py-1" style="font-size: 0.95rem;">
                                                {{ number_format($item['p_final'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted font-italic">En curso</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td class="text-left pl-4 text-uppercase">Promedio General del Grado Actual</td>
                                    <td colspan="3" class="text-muted text-right pr-3 font-weight-normal">
                                        Aprobadas: <span class="badge badge-success">{{ $boletaData['materias_aprobadas'] }}</span>
                                        @if($boletaData['materias_reprobadas'] > 0)
                                            | Reprobadas: <span class="badge badge-danger">{{ $boletaData['materias_reprobadas'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-2" style="font-size: 1.1rem;">
                                        @if(!is_null($boletaData['promedio_general']))
                                            <span class="badge badge-pill {{ $boletaData['promedio_general'] >= 8.0 ? 'badge-success' : ($boletaData['promedio_general'] >= 6.0 ? 'badge-info' : 'badge-danger') }} px-3 py-1">
                                                {{ number_format($boletaData['promedio_general'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-info-circle fa-2x mb-3 text-info"></i>
                        <p class="mb-0">No se encontraron materias ni calificaciones registradas para este alumno.</p>
                    </div>
                @endif
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <a href="{{ route('boletas.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
                </a>
                <div>
                    <a href="{{ route('boletas.kardex', $alumno->id) }}" class="btn btn-primary mr-2">
                        <i class="fas fa-graduation-cap mr-1"></i> Histórico (Kardex)
                    </a>
                    <a href="{{ route('boletas.pdf', $alumno->id) }}" class="btn btn-danger" target="_blank">
                        <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
