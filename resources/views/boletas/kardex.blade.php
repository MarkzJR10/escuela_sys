@extends('adminlte::page')

@section('title', 'Kardex Escolar - ' . $alumno->matricula)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-graduation-cap text-primary mr-2"></i> Historial Académico (Kardex)</h1>
        <div>
            <a href="{{ route('boletas.show', $alumno->id) }}" class="btn btn-outline-success mr-2">
                <i class="fas fa-file-invoice mr-1"></i> Boleta Actual
            </a>
            <button type="button" class="btn btn-outline-primary mr-2" data-toggle="modal" data-target="#modalAgregarHistorico">
                <i class="fas fa-plus-circle mr-1"></i> Agregar Registro Histórico
            </button>
            <a href="{{ route('boletas.kardex_pdf', $alumno->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Descargar Kardex PDF
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-11 mx-auto">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-1"></i> Por favor revise los errores en el formulario.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Ficha de Información del Alumno -->
        <div class="card card-outline card-primary shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        @if($alumno->fotografia)
                            <img class="img-circle elevation-2" src="{{ asset('storage/'.$alumno->fotografia) }}" alt="Avatar" style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center elevation-2" style="width: 80px; height: 80px; font-size: 2.2rem;">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col">
                        <h3 class="mb-1 font-weight-bold text-dark">{{ $alumno->nombre_completo }}</h3>
                        <div class="text-muted d-flex flex-wrap">
                            <span class="mr-3"><i class="fas fa-id-card text-muted mr-1"></i> Matrícula: <strong><code>{{ $alumno->matricula }}</code></strong></span>
                            @if($alumno->curp)
                                <span class="mr-3"><i class="fas fa-fingerprint text-muted mr-1"></i> CURP: <strong>{{ $alumno->curp }}</strong></span>
                            @endif
                            <span class="mr-3"><i class="fas fa-school text-muted mr-1"></i> Grado Actual: <strong>{{ $alumno->gradoGrupo ? $alumno->gradoGrupo->grado . ' "' . $alumno->gradoGrupo->grupo . '"' : 'Sin asignar' }}</strong></span>
                            <span><i class="fas fa-user-tag text-muted mr-1"></i> Estatus: <span class="badge badge-success text-uppercase">{{ $alumno->estatus ?: 'Regular' }}</span></span>
                        </div>
                    </div>
                    <div class="col-auto text-right border-left pl-4">
                        <small class="text-muted text-uppercase font-weight-bold d-block">Promedio General Acumulado</small>
                        @if(!is_null($kardexData['promedio_general_acumulado']))
                            <div class="display-4 font-weight-bold {{ $kardexData['promedio_general_acumulado'] >= 8.0 ? 'text-success' : ($kardexData['promedio_general_acumulado'] >= 6.0 ? 'text-primary' : 'text-danger') }}" style="font-size: 2.6rem; line-height: 1.1;">
                                {{ number_format($kardexData['promedio_general_acumulado'], 1) }}
                            </div>
                            <small class="text-muted">{{ $kardexData['total_materias_cursadas'] }} materias cursadas</small>
                        @else
                            <span class="text-muted font-italic">En proceso</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial Académico Agrupado por Grado -->
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
                            <span class="mr-2 text-muted">
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
                                        <th class="text-left pl-4 py-2" style="width: 35%;">Materia / Asignatura</th>
                                        <th class="py-2" style="width: 15%;">1° Trimestre</th>
                                        <th class="py-2" style="width: 15%;">2° Trimestre</th>
                                        <th class="py-2" style="width: 15%;">3° Trimestre</th>
                                        <th class="py-2 bg-light font-weight-bold" style="width: 15%;">Promedio Final</th>
                                        <th class="py-2" style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($gradoInfo['materias'] as $m)
                                        <tr>
                                            <td class="text-left pl-4 font-weight-bold text-dark align-middle">
                                                <i class="fas fa-book text-muted mr-2"></i> {{ $m['materia'] }}
                                                @if(isset($m['fuente']) && $m['fuente'] === 'calificaciones')
                                                    <span class="badge badge-light text-muted border ml-1 font-weight-normal" style="font-size: 0.75rem;">En curso</span>
                                                @endif
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
                                            <td class="align-middle text-right pr-3">
                                                @if(!empty($m['id']))
                                                    <form action="{{ route('boletas.kardex.destroy_historico', $m['id']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este registro histórico?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-outline-danger" title="Eliminar registro histórico">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-3 text-muted">No hay materias registradas para este grado.</td>
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
                                        <td></td>
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
                    <h4>Aún no hay calificaciones registradas para el Kardex</h4>
                    <p class="mb-3">Puedes agregar materias y calificaciones históricas de grados anteriores haciendo clic en el botón de abajo.</p>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarHistorico">
                        <i class="fas fa-plus-circle mr-1"></i> Registrar Calificación Histórica
                    </button>
                </div>
            </div>
        @endif

        <div class="text-center mt-4 mb-5">
            <a href="{{ route('boletas.index') }}" class="btn btn-default mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Boletas
            </a>
            <a href="{{ route('boletas.kardex_pdf', $alumno->id) }}" class="btn btn-danger" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Descargar Kardex Completo (PDF)
            </a>
        </div>
    </div>
</div>

<!-- Modal para agregar calificación histórica -->
<div class="modal fade" id="modalAgregarHistorico" tabindex="-1" role="dialog" aria-labelledby="modalAgregarHistoricoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('boletas.kardex.store_historico', $alumno->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalAgregarHistoricoLabel">
                        <i class="fas fa-plus-circle mr-1"></i> Registrar Materia en Kardex
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="grado">Grado Escolar <span class="text-danger">*</span></label>
                        <select name="grado" id="grado" class="form-control" required>
                            <option value="">Seleccione el grado...</option>
                            <option value="1ro">1° Grado (1ro)</option>
                            <option value="2do">2° Grado (2do)</option>
                            <option value="3ro">3° Grado (3ro)</option>
                            <option value="4to">4° Grado (4to)</option>
                            <option value="5to">5° Grado (5to)</option>
                            <option value="6to">6° Grado (6to)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ciclo">Ciclo Escolar</label>
                        <input type="text" name="ciclo" id="ciclo" class="form-control" placeholder="Ej. 2024-2025" value="{{ date('Y')-1 }}-{{ date('Y') }}">
                    </div>

                    <div class="form-group">
                        <label for="materia">Nombre de la Asignatura / Materia <span class="text-danger">*</span></label>
                        <input type="text" name="materia" id="materia" class="form-control" list="materiasList" placeholder="Ej. Español, Matemáticas..." required>
                        <datalist id="materiasList">
                            @foreach($materiasCatalog as $mat)
                                <option value="{{ $mat->nombre }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-group">
                                <label for="p1">1° Trimestre</label>
                                <input type="number" step="0.1" min="0" max="10" name="p1" id="p1" class="form-control text-center" placeholder="0-10">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="p2">2° Trimestre</label>
                                <input type="number" step="0.1" min="0" max="10" name="p2" id="p2" class="form-control text-center" placeholder="0-10">
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label for="p3">3° Trimestre</label>
                                <input type="number" step="0.1" min="0" max="10" name="p3" id="p3" class="form-control text-center" placeholder="0-10">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="p_final">Promedio Final <small class="text-muted">(opcional, si se deja vacío se calcula el promedio de trimestres)</small></label>
                        <input type="number" step="0.1" min="0" max="10" name="p_final" id="p_final" class="form-control" placeholder="Ej. 9.5">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar en Kardex</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop
