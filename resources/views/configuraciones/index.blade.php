@extends('adminlte::page')

@section('title', 'Configuración General')

@section('content_header')
    <h1>Configuración General del Sistema</h1>
@stop

@section('content')
    <form action="{{ route('configuraciones.update') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-cogs mr-1"></i> Parámetros de Inscripción</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
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
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-shield mr-1"></i> Visibilidad Portal Padre</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Activa o desactiva las opciones a las que tendrán acceso los padres de familia en su portal:</p>

                        <div class="form-group border-bottom pb-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="checkbox" class="custom-control-input" id="portal_padre_ver_boleta" name="portal_padre_ver_boleta" value="1" {{ $configs['portal_padre_ver_boleta'] == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="portal_padre_ver_boleta">
                                    <i class="fas fa-file-pdf text-danger mr-1"></i> Boleta de Calificaciones
                                </label>
                            </div>
                            <small class="text-muted pl-4 d-block mt-1">Permite a los tutores consultar y descargar la boleta de calificaciones de sus hijos.</small>
                        </div>

                        <div class="form-group border-bottom pb-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="checkbox" class="custom-control-input" id="portal_padre_ver_conducta" name="portal_padre_ver_conducta" value="1" {{ $configs['portal_padre_ver_conducta'] == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="portal_padre_ver_conducta">
                                    <i class="fas fa-exclamation-triangle text-warning mr-1"></i> Reportes de Conducta
                                </label>
                            </div>
                            <small class="text-muted pl-4 d-block mt-1">Permite a los tutores visualizar los reportes de conducta registrados.</small>
                        </div>

                        <div class="form-group border-bottom pb-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="checkbox" class="custom-control-input" id="portal_padre_ver_estado_cuenta" name="portal_padre_ver_estado_cuenta" value="1" {{ $configs['portal_padre_ver_estado_cuenta'] == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="portal_padre_ver_estado_cuenta">
                                    <i class="fas fa-file-invoice-dollar text-success mr-1"></i> Estado de Cuenta
                                </label>
                            </div>
                            <small class="text-muted pl-4 d-block mt-1">Permite a los tutores ver el desglose de adeudos pendientes y saldos.</small>
                        </div>

                        <div class="form-group pb-2">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="checkbox" class="custom-control-input" id="portal_padre_ver_recibos" name="portal_padre_ver_recibos" value="1" {{ $configs['portal_padre_ver_recibos'] == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="portal_padre_ver_recibos">
                                    <i class="fas fa-receipt text-info mr-1"></i> Recibos de Pago
                                </label>
                            </div>
                            <small class="text-muted pl-4 d-block mt-1">Permite a los tutores generar e imprimir la ficha de recibo bancario.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mb-4">
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    <i class="fas fa-save mr-1"></i> Guardar Configuración
                </button>
            </div>
        </div>
    </form>
@stop
