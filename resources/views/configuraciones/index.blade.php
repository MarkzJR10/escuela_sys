@extends('adminlte::page')

@section('title', 'Configuración General')

@section('content_header')
    <h1>Configuración General del Sistema</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs mr-1"></i> Parámetros de Inscripción</h3>
                </div>
                <form action="{{ route('configuraciones.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="costo_inscripcion">Costo de Inscripción ($)</label>
                            <input type="number" step="0.01" name="costo_inscripcion" class="form-control" id="costo_inscripcion" value="{{ $configs['costo_inscripcion'] }}" required>
                            <small class="text-muted">Este monto se cargará automáticamente como adeudo a cada alumno nuevo.</small>
                        </div>

                        <div class="form-group">
                            <label for="costo_reinscripcion">Costo de Reinscripción ($)</label>
                            <input type="number" step="0.01" name="costo_reinscripcion" class="form-control" id="costo_reinscripcion" value="{{ $configs['costo_reinscripcion'] }}" required>
                            <small class="text-muted">Este monto se cargará al generar adeudos de reinscripción masivos para alumnos activos.</small>
                        </div>

                        <div class="form-group">
                            <label for="costo_papeleria">Costo de Papelería ($)</label>
                            <input type="number" step="0.01" name="costo_papeleria" class="form-control" id="costo_papeleria" value="{{ $configs['costo_papeleria'] }}" required>
                            <small class="text-muted">Este monto se cargará automáticamente al inscribir un nuevo alumno si es mayor a $0.</small>
                        </div>

                        <div class="form-group">
                            <label for="costo_seguro_escolar">Costo de Seguro Escolar ($)</label>
                            <input type="number" step="0.01" name="costo_seguro_escolar" class="form-control" id="costo_seguro_escolar" value="{{ $configs['costo_seguro_escolar'] }}" required>
                            <small class="text-muted">Este monto se cargará automáticamente al inscribir un nuevo alumno si es mayor a $0.</small>
                        </div>

                        <div class="form-group">
                            <label for="costo_cuota_limpieza">Costo de Cuota de Limpieza General ($)</label>
                            <input type="number" step="0.01" name="costo_cuota_limpieza" class="form-control" id="costo_cuota_limpieza" value="{{ $configs['costo_cuota_limpieza'] }}" required>
                            <small class="text-muted">Este monto se cargará automáticamente al inscribir un nuevo alumno si es mayor a $0.</small>
                        </div>

                        <div class="form-group">
                            <label for="ciclo_actual">Ciclo Escolar Actual</label>
                            <input type="text" name="ciclo_actual" class="form-control" id="ciclo_actual" value="{{ $configs['ciclo_actual'] }}" placeholder="Ej: 2025-2026" required>
                            <small class="text-muted">Se utilizará para el concepto del adeudo (Ej: Inscripción 2025-2026).</small>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save mr-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
