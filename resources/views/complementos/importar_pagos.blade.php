@extends('adminlte::page')

@section('title', 'Importar Pagos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-file-import text-primary mr-2"></i> Carga Masiva de Pagos (Excel / CSV)</h1>
        <div>
            <a href="{{ route('reportes.saldo_insuficiente') }}" class="btn btn-outline-warning mr-2">
                <i class="fas fa-exclamation-triangle mr-1"></i> Ver Reporte Saldo Insuficiente
            </a>
            <a href="{{ route('complementos.importar_pagos.ejemplo') }}" class="btn btn-success">
                <i class="fas fa-file-download mr-1"></i> Descargar Ejemplo (.csv)
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle mr-1"></i> {{ session('info') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Formulario de Carga -->
    <div class="col-md-5">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-upload mr-1"></i> 1. Seleccionar Archivo</h3>
            </div>
            <form action="{{ route('complementos.importar_pagos.post') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="callout callout-info mb-3">
                        <h5><i class="fas fa-info-circle mr-1"></i> Formato de Campos Requeridos:</h5>
                        <ul class="pl-3 mb-2 small">
                            <li><code>Fecha de pago</code>: Fecha de la transacción (ej: <code>YYYY-MM-DD</code>).</li>
                            <li><code>Referencia</code> / <code>Referencia Leyenda</code>: Texto con la <strong>nomenclatura de 12 caracteres</strong>.</li>
                            <li><code>Abono</code>: Cantidad económica abonada.</li>
                        </ul>
                        <div class="bg-light p-2 rounded border mb-0 small">
                            <strong>Formato Nomenclatura (12 caracteres):</strong><br>
                            <code>[Matrícula 5ch][Grado 1ch][Mes 2ch][Año 4ch]</code><br>
                            <em>Ejemplo: <code>202411092026</code> &rarr; Matrícula: 20241, Grado: 1, Mes: 09, Año: 2026</em>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="archivo_excel">Archivo Excel o CSV (.xlsx, .xls, .csv)</label>
                        <input type="file" name="archivo_excel" id="archivo_excel" class="form-control-file" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search-dollar mr-1"></i> Procesar Vista Previa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen / Vista Previa -->
    <div class="col-md-7">
        @if($preview)
            @php
                $countComp = count($preview['completos']);
                $countInsuf = count($preview['insuficientes']);
                $countErr = count($preview['errores']);
                $totalAbonoComp = array_sum(array_column($preview['completos'], 'monto_abonado'));
                $totalAbonoInsuf = array_sum(array_column($preview['insuficientes'], 'monto_abonado'));
                $totalDiferencia = array_sum(array_column($preview['insuficientes'], 'diferencia'));
            @endphp

            <div class="card card-success card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-tasks mr-1"></i> 2. Resumen y Confirmación</h3>
                </div>
                <div class="card-body">
                    <!-- Tarjetas de métricas rápidas -->
                    <div class="row text-center mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-2 bg-light">
                                <span class="text-muted small d-block">Pagos a Aplicar</span>
                                <strong class="h4 text-success">{{ $countComp }}</strong>
                                <small class="d-block text-muted">${{ number_format($totalAbonoComp, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-2 bg-light">
                                <span class="text-muted small d-block">Saldo Insuficiente</span>
                                <strong class="h4 text-warning">{{ $countInsuf }}</strong>
                                <small class="d-block text-danger">Faltante: ${{ number_format($totalDiferencia, 2) }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-2 bg-light">
                                <span class="text-muted small d-block">Errores / No Coinciden</span>
                                <strong class="h4 text-danger">{{ $countErr }}</strong>
                                <small class="d-block text-muted">No procesables</small>
                            </div>
                        </div>
                    </div>

                    <!-- Pestañas de detalle -->
                    <ul class="nav nav-tabs" id="previewTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-completo" data-toggle="tab" href="#content-completo" role="tab">
                                <i class="fas fa-check-circle text-success mr-1"></i> A Aplicar ({{ $countComp }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-insuficiente" data-toggle="tab" href="#content-insuficiente" role="tab">
                                <i class="fas fa-exclamation-triangle text-warning mr-1"></i> Saldo Insuficiente ({{ $countInsuf }})
                            </a>
                        </li>
                        @if($countErr > 0)
                        <li class="nav-item">
                            <a class="nav-link" id="tab-error" data-toggle="tab" href="#content-error" role="tab">
                                <i class="fas fa-times-circle text-danger mr-1"></i> Errores ({{ $countErr }})
                            </a>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content border-left border-right border-bottom p-3 bg-white" id="previewTabsContent">
                        <!-- Pestaña Completo -->
                        <div class="tab-pane fade show active" id="content-completo" role="tabpanel">
                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Matrícula</th>
                                            <th>Alumno</th>
                                            <th>Periodo</th>
                                            <th>Abono</th>
                                            <th>Debía (Monto Requerido)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($preview['completos'] as $item)
                                            <tr>
                                                <td><code>{{ $item['matricula'] }}</code></td>
                                                <td>{{ $item['alumno_nombre'] }}</td>
                                                <td><span class="badge badge-info">{{ $item['periodo'] }}</span></td>
                                                <td class="text-success font-weight-bold">${{ number_format($item['monto_abonado'], 2) }}</td>
                                                <td>${{ number_format($item['monto_debido'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No hay pagos completos para aplicar.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pestaña Insuficiente -->
                        <div class="tab-pane fade" id="content-insuficiente" role="tabpanel">
                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Matrícula</th>
                                            <th>Alumno</th>
                                            <th>Periodo</th>
                                            <th>Abonado</th>
                                            <th>Monto Requerido</th>
                                            <th>Faltante</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($preview['insuficientes'] as $item)
                                            <tr>
                                                <td><code>{{ $item['matricula'] }}</code></td>
                                                <td>{{ $item['alumno_nombre'] }}</td>
                                                <td><span class="badge badge-warning">{{ $item['periodo'] }}</span></td>
                                                <td class="text-warning font-weight-bold">${{ number_format($item['monto_abonado'], 2) }}</td>
                                                <td>${{ number_format($item['monto_debido'], 2) }}</td>
                                                <td class="text-danger font-weight-bold">${{ number_format($item['diferencia'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No hay pagos con saldo insuficiente.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pestaña Errores -->
                        @if($countErr > 0)
                        <div class="tab-pane fade" id="content-error" role="tabpanel">
                            <div class="table-responsive" style="max-height: 300px;">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Fila</th>
                                            <th>Ref / Leyenda</th>
                                            <th>Abono</th>
                                            <th>Motivo Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($preview['errores'] as $item)
                                            <tr>
                                                <td><span class="badge badge-secondary">Fila {{ $item['fila'] }}</span></td>
                                                <td><small>{{ $item['referencia'] }} {{ $item['referencia_leyenda'] }}</small></td>
                                                <td>${{ number_format($item['abono'], 2) }}</td>
                                                <td class="text-danger"><small>{{ $item['motivo'] }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <span class="text-muted small align-self-center">
                        <i class="fas fa-info-circle"></i> Los pagos completos se aplicarán en caja y los incompletos pasarán al reporte.
                    </span>
                    <form action="{{ route('complementos.importar_pagos.confirmar') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg font-weight-bold" @if($countComp == 0 && $countInsuf == 0) disabled @endif>
                            <i class="fas fa-check-circle mr-1"></i> Confirmar y Aplicar Operación
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="card card-outline card-secondary">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-file-excel fa-3x mb-3 text-secondary"></i>
                    <h4>Sin Vista Previa Generada</h4>
                    <p class="mb-0">Sube un archivo en el panel izquierdo para analizar los datos y ver el resumen antes de aplicar los cambios.</p>
                </div>
            </div>
        @endif
    </div>
</div>
@stop
