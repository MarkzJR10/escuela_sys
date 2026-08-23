@extends('adminlte::page')

@section('title', 'Clases Extracurriculares')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-running text-primary mr-2"></i> Gestión de Clases Extracurriculares</h1>
    </div>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Tarjetas KPI -->
<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="small-box bg-primary shadow-sm">
            <div class="inner">
                <h3>{{ number_format($totalGenerado) }}</h3>
                <p>Total Adeudos Generados</p>
            </div>
            <div class="icon">
                <i class="fas fa-list-alt"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-warning shadow-sm text-white">
            <div class="inner text-white">
                <h3>{{ number_format($totalPendientes) }}</h3>
                <p class="text-white">Adeudos Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-success shadow-sm">
            <div class="inner">
                <h3>{{ number_format($totalPagados) }}</h3>
                <p>Adeudos Pagados</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-info shadow-sm">
            <div class="inner">
                <h3>${{ number_format($montoTotalPendiente, 2) }}</h3>
                <p>Monto Pendiente por Cobrar</p>
            </div>
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>
</div>

<!-- Selector de Ciclo Escolar -->
<div class="card card-primary card-outline shadow-sm mb-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-search mr-1"></i> Seleccionar Ciclo Escolar</h3>
    </div>
    <div class="card-body py-3">
        <form method="GET" action="{{ route('extracurriculares.index') }}" class="form-inline justify-content-center">
            <label for="ciclo_select" class="mr-2 font-weight-bold">Ciclo Escolar:</label>
            <select name="ciclo" id="ciclo_select" class="form-control mr-3">
                @foreach($opcionesCiclo as $opcion)
                    <option value="{{ $opcion }}" {{ $cicloSeleccionado === $opcion ? 'selected' : '' }}>
                        {{ $opcion }} {{ $cicloActual === $opcion ? '(Ciclo Vigente)' : '' }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Consultar Ciclo</button>
        </form>
    </div>
</div>

<!-- Panel de Control y Acciones -->
<div class="card card-outline card-primary shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">
            <i class="fas fa-tools mr-1"></i> Panel de Acciones - Concepto: <strong>CLASES EXTRACURRICULARES</strong>
        </h3>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-center">
            <!-- Botón Generar Adeudos -->
            <button type="button" class="btn btn-success px-4 py-2 m-2 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalGenerarAdeudos">
                <i class="fas fa-plus-circle mr-1"></i> Generar Adeudos Extracurriculares
            </button>

            <!-- Botón Configurar Costo por Alumno -->
            <button type="button" class="btn btn-info px-4 py-2 m-2 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalTarifasAlumnos">
                <i class="fas fa-user-edit mr-1"></i> Configurar Costos por Alumno
            </button>

            <!-- Botón Quitar Adeudos -->
            <button type="button" class="btn btn-danger px-4 py-2 m-2 font-weight-bold shadow-sm" data-toggle="modal" data-target="#modalQuitarAdeudos">
                <i class="fas fa-trash-alt mr-1"></i> Quitar Adeudos
            </button>
        </div>
    </div>
</div>

<!-- Tabla Principal DataTables de Alumnos / Adeudos -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-table mr-1"></i> Lista de Adeudos Registrados - Clases Extracurriculares
        </h3>
    </div>
    <div class="card-body">
        @if($adeudos->count() > 0)
            <div class="table-responsive">
                <table id="tblExtracurriculares" class="table table-bordered table-striped text-sm w-100">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>Matrícula</th>
                            <th>Alumno</th>
                            <th>Grado y Grupo</th>
                            <th>Concepto / Periodo</th>
                            <th class="text-right">Monto ($)</th>
                            <th class="text-center">Estatus</th>
                            <th class="text-center" style="width: 220px;">Acciones Individuales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adeudos as $adeudo)
                        <tr>
                            <td><span class="badge badge-light border">{{ optional($adeudo->alumno)->matricula ?? 'N/A' }}</span></td>
                            <td>
                                <strong>{{ optional($adeudo->alumno)->apellido_paterno }} {{ optional($adeudo->alumno)->apellido_materno }} {{ optional($adeudo->alumno)->nombre }}</strong>
                            </td>
                            <td>{{ optional(optional($adeudo->alumno)->gradoGrupo)->grado }} "{{ optional(optional($adeudo->alumno)->gradoGrupo)->grupo }}"</td>
                            <td>
                                <span class="badge badge-info">{{ $adeudo->concepto }}</span>
                            </td>
                            <td class="text-right font-weight-bold text-dark">${{ number_format($adeudo->monto_actual, 2) }}</td>
                            <td class="text-center">
                                @if($adeudo->status === 'pagado')
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i> Pagado</span>
                                @elseif($adeudo->status === 'cancelado')
                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban mr-1"></i> Cancelado</span>
                                @else
                                    <span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-clock mr-1"></i> Pendiente</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($adeudo->status !== 'pagado')
                                    <!-- Botón Refrescar Adeudo Individual -->
                                    <form action="{{ route('extracurriculares.refrescar_individual', $adeudo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Refrescar el monto de este adeudo al costo extracurricular actual del alumno?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-info mr-1" title="Refrescar / Recalcular monto al costo vigente">
                                            <i class="fas fa-sync-alt"></i> Refrescar
                                        </button>
                                    </form>

                                    <!-- Botón Cancelar Adeudo Individual -->
                                    <form action="{{ route('extracurriculares.cancelar_individual', $adeudo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar/cancelar este adeudo de Clases Extracurriculares?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Cancelar / Quitar este adeudo individual">
                                            <i class="fas fa-trash-alt"></i> Cancelar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted"><i class="fas fa-lock mr-1"></i> Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 text-center">
                <i class="fas fa-running fa-3x text-muted mb-3"></i>
                <p class="text-muted h5">No hay adeudos generados para Clases Extracurriculares.</p>
                <p class="text-muted">Presiona <strong>"Generar Adeudos Extracurriculares"</strong> para comenzar a generar los adeudos del ciclo.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: Generar Adeudos Extracurriculares (Regla 1 y 4) -->
<div class="modal fade" id="modalGenerarAdeudos" tabindex="-1" role="dialog" aria-labelledby="modalGenerarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('extracurriculares.generar') }}" method="POST" onsubmit="return confirm('¿Desea generar los adeudos de Clases Extracurriculares para todos los alumnos? Solo se crearán los periodos pendientes.');">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="modalGenerarLabel"><i class="fas fa-plus-circle mr-1"></i> Generar Adeudos - Clases Extracurriculares</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="ciclo">Ciclo Escolar:</label>
                        <select name="ciclo" id="ciclo" class="form-control">
                            @foreach($opcionesCiclo as $opc)
                                <option value="{{ $opc }}" {{ $cicloSeleccionado === $opc ? 'selected' : '' }}>{{ $opc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="monto_default">Tarifa Mensual por Defecto ($) (Opcional):</label>
                        <input type="number" step="0.01" min="0" name="monto_default" id="monto_default" class="form-control" placeholder="Ej. 500.00">
                        <small class="form-text text-muted">Si un alumno no tiene una tarifa individual configurada, se usará este monto por defecto.</small>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-1"></i> <strong>Reglas aplicadas:</strong> Se generará para todos los alumnos. Se considerarán solo los periodos pendientes sin duplicar adeudos existentes.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success font-weight-bold"><i class="fas fa-check mr-1"></i> Generar Adeudos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Configurar Costo por Alumno (Regla 3) -->
<div class="modal fade" id="modalTarifasAlumnos" tabindex="-1" role="dialog" aria-labelledby="modalTarifasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalTarifasLabel"><i class="fas fa-user-edit mr-1"></i> Configurar Costo de Clases Extracurriculares por Alumno</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Asignación Masiva Rápida -->
                <div class="card card-light mb-3 border">
                    <div class="card-body">
                        <form action="{{ route('extracurriculares.actualizar_montos_masivo') }}" method="POST" class="form-inline">
                            @csrf
                            <label class="mr-2"><strong>Asignar Tarifa General a Todos:</strong></label>
                            <input type="number" step="0.01" min="0" name="monto_general" class="form-control mr-2" placeholder="Ej. 600.00" required>
                            <button type="submit" class="btn btn-info"><i class="fas fa-save mr-1"></i> Aplicar a Todos</button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="tblTarifasAlumnos" class="table table-bordered table-striped text-sm w-100">
                        <thead class="bg-info text-white">
                            <tr>
                                <th>Matrícula</th>
                                <th>Alumno</th>
                                <th>Grado y Grupo</th>
                                <th>Monto Actual Clases Extracurriculares ($)</th>
                                <th class="text-center" style="width: 180px;">Guardar Cambios</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alumnos as $al)
                            <tr>
                                <td><span class="badge badge-light border">{{ $al->matricula }}</span></td>
                                <td><strong>{{ $al->apellido_paterno }} {{ $al->apellido_materno }} {{ $al->nombre }}</strong></td>
                                <td>{{ optional($al->gradoGrupo)->grado }} "{{ optional($al->gradoGrupo)->grupo }}"</td>
                                <td>
                                    <form id="formMonto-{{ $al->id }}" action="{{ route('extracurriculares.update_monto_alumno', $al->id) }}" method="POST" class="d-flex">
                                        @csrf
                                        <input type="number" step="0.01" min="0" name="monto_extracurricular" class="form-control form-control-sm font-weight-bold" value="{{ $al->monto_extracurricular }}" required>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <button type="submit" form="formMonto-{{ $al->id }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-save mr-1"></i> Guardar Costo
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Quitar Adeudos Masivos / Por Alumno (Regla 2) -->
<div class="modal fade" id="modalQuitarAdeudos" tabindex="-1" role="dialog" aria-labelledby="modalQuitarLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('extracurriculares.eliminar_masivo') }}" method="POST" onsubmit="return confirm('¿Está seguro de remover los adeudos seleccionados de Clases Extracurriculares? Esta acción no afectará cargos pagados.');">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalQuitarLabel"><i class="fas fa-trash-alt mr-1"></i> Quitar Adeudos - Clases Extracurriculares</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="ciclo_quitar">Ciclo Escolar:</label>
                        <select name="ciclo" id="ciclo_quitar" class="form-control">
                            @foreach($opcionesCiclo as $opc)
                                <option value="{{ $opc }}" {{ $cicloSeleccionado === $opc ? 'selected' : '' }}>{{ $opc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="alumno_id_quitar">Alumno Específico (Opcional):</label>
                        <select name="alumno_id" id="alumno_id_quitar" class="form-control select2" style="width: 100%;">
                            <option value="">-- Todos los Alumnos (Masivo) --</option>
                            @foreach($alumnos as $al)
                                <option value="{{ $al->id }}">{{ $al->apellido_paterno }} {{ $al->apellido_materno }} {{ $al->nombre }} ({{ $al->matricula }})</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Seleccione un alumno si desea eliminar solo sus cargos pendientes de Clases Extracurriculares.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger font-weight-bold"><i class="fas fa-trash-alt mr-1"></i> Confirmar Eliminación</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('plugins.Select2', true)
@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#tblExtracurriculares').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 25,
            "responsive": true,
            "order": [[0, "asc"]],
            "columnDefs": [
                { "orderable": false, "targets": [6] }
            ]
        });

        $('#tblTarifasAlumnos').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "order": [[1, "asc"]]
        });

        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    });
</script>
@stop
