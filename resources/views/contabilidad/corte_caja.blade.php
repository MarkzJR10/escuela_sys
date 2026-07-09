@extends('adminlte::page')

@section('title', 'Corte de Caja')

@section('content_header')
    <h1>Corte de Caja</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="icon fas fa-ban"></i> ¡Atención!</h5>
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Resumen de Caja Actual (Pendiente de Corte) -->
    <div class="row">
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box bg-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-hand-holding-usd"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Total Cobrado</span>
                    <span class="info-box-number h4">${{ number_format($totalCobrado, 2) }}</span>
                    <span class="text-xs">{{ $pagosPendientes->count() }} ticket(s) pendiente(s)</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box bg-warning shadow-sm">
                <span class="info-box-icon text-white"><i class="fas fa-file-invoice-dollar text-white"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Total Gastado</span>
                    <span class="info-box-number h4">${{ number_format($totalGastado, 2) }}</span>
                    <span class="text-xs">{{ $gastosPendientes->count() }} gasto(s) pendiente(s)</span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 col-12">
            <div class="info-box bg-info shadow-sm">
                <span class="info-box-icon"><i class="fas fa-cash-register"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text font-weight-bold">Balance Neto</span>
                    <span class="info-box-number h4">${{ number_format($totalCobrado - $totalGastado, 2) }}</span>
                    <span class="text-xs">Efectivo actual en caja</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Acción: Realizar Corte -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="font-weight-bold mb-1">Cierre de Turno / Caja</h5>
                        <p class="text-muted mb-0">Al realizar el corte, todas tus ventas y gastos pendientes se agruparán en un reporte y la caja actual volverá a ceros.</p>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <form id="corteForm" action="{{ route('contabilidad.corte_caja.store') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                <i class="fas fa-cut mr-2"></i> Realizar Corte de Caja
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Cortes -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2"></i> Historial de Cortes de Caja</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="cortes-table" class="table table-bordered table-striped table-hover text-center">
                            <thead>
                                <tr>
                                    <th>Folio</th>
                                    <th>Cajero</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Cobrado</th>
                                    <th>Gastado</th>
                                    <th>Balance Neto</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cortes as $corte)
                                <tr>
                                    <td class="font-weight-bold">#{{ str_pad($corte->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $corte->cajero->name }}</td>
                                    <td>{{ $corte->fecha_inicio ? $corte->fecha_inicio->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                    <td>{{ $corte->fecha_fin ? $corte->fecha_fin->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                    <td class="text-success font-weight-bold">${{ number_format($corte->total_cobrado, 2) }}</td>
                                    <td class="text-danger font-weight-bold">${{ number_format($corte->total_gastado, 2) }}</td>
                                    <td class="font-weight-bold">${{ number_format($corte->total_cobrado - $corte->total_gastado, 2) }}</td>
                                    <td>
                                        <a href="{{ route('contabilidad.corte_caja.pdf', $corte->id) }}" target="_blank" class="btn btn-danger btn-sm shadow-sm">
                                            <i class="fas fa-file-pdf mr-1"></i> Imprimir PDF
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#cortes-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "order": [[0, "desc"]],
            "pageLength": 10,
            "responsive": true
        });

        // Apertura automática del PDF si viene en la sesión
        @if(session('open_corte_pdf_url'))
            window.open("{{ session('open_corte_pdf_url') }}", "_blank");
        @endif

        // Manejar la confirmación de corte al enviar el formulario
        $('#corteForm').on('submit', function(e) {
            e.preventDefault(); // Detener el envío automático

            let cobrado = {{ $totalCobrado }};
            let gastado = {{ $totalGastado }};

            if (cobrado === 0 && gastado === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Caja sin entradas',
                    text: 'No tienes cobros ni gastos registrados para realizar un corte.',
                    confirmButtonColor: '#3085d6',
                });
                return;
            }

            Swal.fire({
                title: '¿Confirmar Corte de Caja?',
                text: "Se procesará el cierre de caja actual y se generará el PDF correspondiente.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, realizar corte',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.value) {
                    // Enviar formulario nativamente
                    document.getElementById('corteForm').submit();
                }
            });
        });
    });
</script>
@stop
