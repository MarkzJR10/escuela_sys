@extends('adminlte::page')

@section('title', 'Ciclos Masivo')

@section('content_header')
    <h1><i class="fas fa-calendar-alt text-primary"></i> Ciclos Masivo</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
    </div>
@endif

<div class="row">
    <div class="col-md-10 mx-auto">
        {{-- Selector de ciclo --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Seleccionar Ciclo Escolar</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('ciclos.index') }}" class="form-inline justify-content-center">
                    <div class="form-group mr-3">
                        <label for="ciclo" class="mr-2"><strong>Ciclo:</strong></label>
                        <select name="ciclo" id="ciclo" class="form-control">
                            <option value="">Seleccione...</option>
                            @foreach($opciones as $opcion)
                                <option value="{{ $opcion }}" {{ $cicloSeleccionado === $opcion ? 'selected' : '' }}>
                                    {{ $opcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </form>

                @if($cicloSeleccionado)
                    <hr>
                    <div class="text-center">
                        <p class="text-muted mb-3">
                            Ciclo vigente en configuración: <strong>{{ $cicloActual }}</strong>
                        </p>
                        <div class="d-flex flex-wrap justify-content-center">
                            <form method="POST" action="{{ route('ciclos.registrar_masivo') }}"
                                  onsubmit="return confirm('¿Está seguro de registrar adeudos masivos para el ciclo {{ $cicloSeleccionado }}?\n\nSe crearán adeudos de colegiatura (Sep-Jun) solo para los alumnos regulares activos que no los tengan aún.');"
                                  class="m-2">
                                @csrf
                                <input type="hidden" name="ciclo" value="{{ $cicloSeleccionado }}">
                                <button type="submit" class="btn btn-success px-3">
                                    <i class="fas fa-plus-circle"></i> Registrar Colegiaturas {{ $cicloSeleccionado }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('ciclos.registrar_reinscripcion_masivo') }}"
                                  onsubmit="return confirm('¿Está seguro de registrar adeudos de reinscripción masivos para el ciclo {{ $cicloSeleccionado }}?\n\nSe creará un adeudo de reinscripción solo para los alumnos regulares activos que no lo tengan aún.');"
                                  class="m-2">
                                @csrf
                                <input type="hidden" name="ciclo" value="{{ $cicloSeleccionado }}">
                                <button type="submit" class="btn btn-warning px-3 text-white">
                                    <i class="fas fa-user-plus"></i> Registrar Reinscripciones {{ $cicloSeleccionado }}
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger px-3 m-2" data-toggle="modal" data-target="#modalEliminarMasivo">
                                <i class="fas fa-trash-alt"></i> Quitar Adeudos del Ciclo
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal de Eliminación Masiva --}}
        @if($cicloSeleccionado)
        <div class="modal fade" id="modalEliminarMasivo" tabindex="-1" role="dialog" aria-labelledby="modalEliminarLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('ciclos.eliminar_masivo') }}" onsubmit="return confirm('¿Está seguro de eliminar los adeudos seleccionados? Esta acción afectará solo adeudos pendientes/no pagados.');">
                        @csrf
                        <input type="hidden" name="ciclo" value="{{ $cicloSeleccionado }}">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalEliminarLabel">
                                <i class="fas fa-exclamation-triangle"></i> Quitar Adeudos Masivamente - Ciclo {{ $cicloSeleccionado }}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted">
                                Seleccione los criterios para remover masivamente los adeudos <strong>pendientes / no pagados</strong> generados para este ciclo escolar.
                            </p>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="estatus_alumno"><strong>Estatus del Alumno:</strong></label>
                                    <select name="estatus_alumno" id="estatus_alumno" class="form-control">
                                        <option value="todos">Todos los alumnos</option>
                                        <option value="baja">Solo Alumnos en Baja</option>
                                        <option value="egresado">Solo Alumnos Egresados</option>
                                        <option value="regular">Solo Alumnos Regulares</option>
                                    </select>
                                    <small class="form-text text-muted">Remueve cargos a los alumnos según su estatus actual.</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tipo_adeudo"><strong>Tipo de Adeudo:</strong></label>
                                    <select name="tipo_adeudo" id="tipo_adeudo" class="form-control">
                                        <option value="todos">Todos (Colegiaturas y Reinscripción)</option>
                                        <option value="colegiatura">Solo Colegiaturas</option>
                                        <option value="especial">Solo Reinscripción</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="alumno_id"><strong>Alumno Específico (Opcional):</strong></label>
                                <select name="alumno_id" id="alumno_id" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Todos los Alumnos --</option>
                                    @foreach($alumnosLista as $al)
                                        <option value="{{ $al->id }}">
                                            {{ $al->apellido_paterno }} {{ $al->apellido_materno }} {{ $al->nombre }} ({{ strtoupper($al->estatus ?? 'REGULAR') }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Si selecciona un alumno en específico, solo se borrarán sus cargos pendientes de este ciclo.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i> Confirmar Eliminación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Tabla de adeudos del ciclo --}}
        @if($cicloSeleccionado)
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Adeudos del Ciclo {{ $cicloSeleccionado }}
                    <span class="badge badge-info ml-2">{{ $adeudos->count() }} registros</span>
                </h3>
            </div>
            <div class="card-body p-0">
                @if($adeudos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" id="tblCiclos">
                            <thead class="bg-info text-white">
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Alumno</th>
                                    <th>Estatus Alumno</th>
                                    <th>Grado</th>
                                    <th>Concepto / Periodo</th>
                                    <th class="text-right">Monto</th>
                                    <th class="text-center">Estatus Pago</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adeudos as $adeudo)
                                <tr>
                                    <td>{{ $adeudo->alumno->matricula ?? 'N/A' }}</td>
                                    <td>
                                        {{ $adeudo->alumno->apellido_paterno ?? '' }}
                                        {{ $adeudo->alumno->apellido_materno ?? '' }}
                                        {{ $adeudo->alumno->nombre ?? '' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $st = strtolower($adeudo->alumno->estatus ?? 'regular');
                                        @endphp
                                        @if($st === 'baja')
                                            <span class="badge badge-danger">Baja</span>
                                        @elseif($st === 'egresado')
                                            <span class="badge badge-secondary">Egresado</span>
                                        @else
                                            <span class="badge badge-success">Regular</span>
                                        @endif
                                    </td>
                                    <td>{{ $adeudo->alumno->gradoGrupo->grado ?? '' }} "{{ $adeudo->alumno->gradoGrupo->grupo ?? '' }}"</td>
                                    <td>
                                        @if($adeudo->tipo === 'colegiatura')
                                            {{ $adeudo->concepto }} <span class="badge badge-secondary ml-1">{{ $adeudo->periodo }}</span>
                                        @else
                                            <span class="font-weight-bold text-primary"><i class="fas fa-award mr-1"></i>{{ $adeudo->concepto }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">${{ number_format($adeudo->monto_base, 2) }}</td>
                                    <td class="text-center">
                                        @if($adeudo->status === 'pagado')
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Pagado</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay adeudos registrados para el ciclo <strong>{{ $cicloSeleccionado }}</strong>.</p>
                        <p class="text-muted">Use los botones de arriba para registrar adeudos de colegiaturas o reinscripción.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(function() {
        if ($('#tblCiclos').length) {
            $('#tblCiclos').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                },
                "pageLength": 10,
                "responsive": true,
                "order": [[1, 'asc']]
            });
        }
    });
</script>
@stop




