@extends('adminlte::page')

@section('title', 'Visibilidad Portal Padres')

@section('content_header')
    <h1><i class="fas fa-user-shield text-info mr-2"></i> Visibilidad Portal Padres</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Módulos Visibles para Padres de Familia</h3>
                </div>
                <form action="{{ route('configuraciones.update_portal_padres') }}" method="POST">
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

                    <div class="card-footer">
                        <button type="submit" class="btn btn-info px-4">
                            <i class="fas fa-save mr-1"></i> Guardar Visibilidad
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
