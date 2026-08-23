@extends('adminlte::page')

@section('title', 'Bitácora de Auditoría')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-history text-primary mr-2"></i> Bitácora de Auditoría del Sistema</h1>
        <span class="badge badge-danger px-3 py-2" style="font-size: 14px;"><i class="fas fa-user-shield mr-1"></i> Acceso Exclusivo Administradores</span>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline card-tabs shadow-sm">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="bitacoraTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold" id="adeudos-tab" data-toggle="pill" href="#tab-adeudos" role="tab" aria-controls="tab-adeudos" aria-selected="true">
                            <i class="fas fa-trash-alt text-danger mr-1"></i> Quitas y Eliminación de Adeudos 
                            <span class="badge badge-danger ml-1">{{ $bitacorasAdeudos->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold" id="stock-tab" data-toggle="pill" href="#tab-stock" role="tab" aria-controls="tab-stock" aria-selected="false">
                            <i class="fas fa-boxes text-success mr-1"></i> Reabastecimiento de Stock 
                            <span class="badge badge-success ml-1">{{ $bitacorasStock->count() }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="bitacoraTabsContent">
                    
                    <!-- TAB 1: ELIMINACIÓN DE ADEUDOS -->
                    <div class="tab-pane fade show active" id="tab-adeudos" role="tabpanel" aria-labelledby="adeudos-tab">
                        @if($bitacorasAdeudos->count() > 0)
                            <div class="table-responsive">
                                <table id="tblAuditAdeudos" class="table table-bordered table-striped text-sm w-100">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Matrícula</th>
                                            <th>Alumno</th>
                                            <th>Ciclo</th>
                                            <th>Usuario que Ejecutó</th>
                                            <th class="text-right">Monto Anterior</th>
                                            <th class="text-right text-danger">Monto Eliminado</th>
                                            <th class="text-right text-success">Monto Nuevo</th>
                                            <th>Meses / Periodos Afectados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bitacorasAdeudos as $b)
                                        <tr>
                                            <td>{{ $b->created_at ? $b->created_at->format('d/m/Y h:i A') : 'N/A' }}</td>
                                            <td><span class="badge badge-light border">{{ $b->matricula ?? 'N/A' }}</span></td>
                                            <td><strong>{{ $b->nombre_alumno }}</strong></td>
                                            <td><span class="badge badge-info">{{ $b->ciclo }}</span></td>
                                            <td>
                                                <i class="fas fa-user-tag text-primary mr-1"></i>{{ optional($b->usuario)->name ?? 'Sistema' }}
                                            </td>
                                            <td class="text-right">${{ number_format($b->monto_anterior, 2) }}</td>
                                            <td class="text-right font-weight-bold text-danger">-${{ number_format($b->monto_eliminado, 2) }}</td>
                                            <td class="text-right font-weight-bold text-success">${{ number_format($b->monto_nuevo, 2) }}</td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $b->meses_afectados }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-1"></i> No hay registros de quitas o eliminación de adeudos en la bitácora de auditoría.
                            </div>
                        @endif
                    </div>

                    <!-- TAB 2: REABASTECIMIENTO DE STOCK -->
                    <div class="tab-pane fade" id="tab-stock" role="tabpanel" aria-labelledby="stock-tab">
                        @if($bitacorasStock->count() > 0)
                            <div class="table-responsive">
                                <table id="tblAuditStock" class="table table-bordered table-striped text-sm w-100">
                                    <thead class="bg-secondary text-white">
                                        <tr>
                                            <th>Fecha y Hora</th>
                                            <th>Producto</th>
                                            <th class="text-center">Cantidad Agregada</th>
                                            <th class="text-center">Stock (Anterior &rarr; Nuevo)</th>
                                            <th>Usuario que Agregó</th>
                                            <th>Motivo / Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($bitacorasStock as $bs)
                                        <tr>
                                            <td>{{ $bs->created_at ? $bs->created_at->format('d/m/Y h:i A') : 'N/A' }}</td>
                                            <td><strong>{{ optional($bs->producto)->nombre ?? 'Producto Eliminado' }}</strong></td>
                                            <td class="text-center font-weight-bold text-success">
                                                <span class="badge badge-success px-2 py-1" style="font-size: 13px;">
                                                    +{{ $bs->cantidad_agregada }} unidades
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">{{ $bs->stock_anterior }}</span>
                                                <i class="fas fa-arrow-right mx-1 text-secondary"></i>
                                                <strong class="text-dark">{{ $bs->stock_nuevo }}</strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-user text-info mr-1"></i>{{ optional($bs->usuario)->name ?? 'Sistema' }}
                                            </td>
                                            <td>{{ $bs->motivo ?: 'Sin observación' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle mr-1"></i> No hay registros de reabastecimiento de stock en la bitácora.
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#tblAuditAdeudos').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 25,
            "responsive": true,
            "order": [[0, "desc"]]
        });

        $('#tblAuditStock').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 25,
            "responsive": true,
            "order": [[0, "desc"]]
        });
    });
</script>
@stop
