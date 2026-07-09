@extends('adminlte::page')

@section('title', 'Discrepancias de Caja')

@section('content_header')
    <h1>Faltantes y Sobrantes (Discrepancias)</h1>
@stop

@section('content')
<div class="row">
    <!-- Registrar Nueva Discrepancia -->
    <div class="col-md-4">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Registrar Discrepancia</h3>
            </div>
            <form action="{{ route('contabilidad.discrepancias.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Monto según Sistema ($)</label>
                        <input type="number" step="0.01" name="monto_sistema" id="monto_sistema" class="form-control" required oninput="calcularDiferencia()">
                    </div>
                    <div class="form-group">
                        <label>Monto Físico (Real en caja) ($)</label>
                        <input type="number" step="0.01" name="monto_fisico" id="monto_fisico" class="form-control" required oninput="calcularDiferencia()">
                    </div>
                    <div class="form-group">
                        <label>Diferencia:</label>
                        <input type="text" id="diferencia_vista" class="form-control font-weight-bold" readonly>
                        <small class="text-muted">Rojo = Faltante | Verde = Sobrante</small>
                    </div>
                    <div class="form-group">
                        <label>Motivo / Observación</label>
                        <textarea name="motivo" class="form-control" rows="3" placeholder="Ej. Cambio mal dado..."></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-save"></i> Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Historial de Discrepancias -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Historial Mensual</h3>
                <div class="card-tools">
                    <form action="{{ route('contabilidad.discrepancias.index') }}" method="GET" class="form-inline">
                        <input type="month" name="mes" class="form-control form-control-sm mr-2" value="{{ $mes }}">
                        <button type="submit" class="btn btn-sm btn-primary">Ver Mes</button>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <table id="discrepancias-table" class="table table-striped table-sm text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Cajero</th>
                            <th>Sistema</th>
                            <th>Físico</th>
                            <th>Diferencia</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($discrepancias as $d)
                        <tr>
                            <td>{{ $d->fecha->format('d/m/Y') }}</td>
                            <td>{{ $d->cajero->name }}</td>
                            <td>${{ number_format($d->monto_sistema, 2) }}</td>
                            <td>${{ number_format($d->monto_fisico, 2) }}</td>
                            <td class="font-weight-bold {{ $d->diferencia < 0 ? 'text-danger' : 'text-success' }}">
                                ${{ number_format($d->diferencia, 2) }}
                            </td>
                            <td class="text-left text-muted small">{{ $d->motivo }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-4 text-success">No hay discrepancias registradas en este mes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    function calcularDiferencia() {
        let sist = parseFloat(document.getElementById('monto_sistema').value) || 0;
        let fis = parseFloat(document.getElementById('monto_fisico').value) || 0;
        let dif = fis - sist;
        let vista = document.getElementById('diferencia_vista');
        
        vista.value = '$' + dif.toFixed(2);
        
        if(dif < 0) {
            vista.className = "form-control font-weight-bold text-white bg-danger";
        } else if(dif > 0) {
            vista.className = "form-control font-weight-bold text-white bg-success";
        } else {
            vista.className = "form-control font-weight-bold";
        }
    }

    $(document).ready(function() {
        if ($('#discrepancias-table tbody tr').length > 1 || !$('#discrepancias-table tbody tr td').hasClass('text-success')) {
            $('#discrepancias-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
                },
                "pageLength": 10,
                "responsive": true,
                "order": [[0, "desc"]] // Sort by date descending by default
            });
        }
    });
</script>
@stop
