@extends('adminlte::page')

@section('title', 'Detalle de Reporte')

@section('content_header')
    <h1>Detalle del Reporte de Conducta #{{ $reporte->no_reporte }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Información del Reporte</h3>
                <div class="card-tools">
                    <span class="badge badge-success">Estatus: {{ ucfirst($reporte->estatus) }}</span>
                </div>
            </div>
            
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted">Alumno:</h6>
                        <h5>{{ $reporte->alumno->nombre }} {{ $reporte->alumno->apellido_paterno }} {{ $reporte->alumno->apellido_materno }}</h5>
                        <p class="mb-0"><strong>Matrícula:</strong> {{ $reporte->alumno->matricula }}</p>
                        <p><strong>Grado y Grupo:</strong> {{ $reporte->alumno->gradoGrupo->grado ?? 'N/A' }} "{{ $reporte->alumno->gradoGrupo->grupo ?? 'N/A' }}"</p>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                        <h6 class="text-muted">Reportado por:</h6>
                        <h5>{{ $reporte->usuario->name }}</h5>
                        <p class="mb-0"><strong>Fecha:</strong> {{ $reporte->fecha->format('d/m/Y') }}</p>
                        <p><strong>Reporte Nivel:</strong> #{{ $reporte->no_reporte }}</p>
                    </div>
                </div>

                <div class="callout callout-danger">
                    <h5>Motivos del Reporte:</h5>
                    <ul>
                        @forelse($reporte->razones_marcadas as $razon)
                            <li>{{ $razon }}</li>
                        @empty
                            <li><em>No se marcaron casillas específicas.</em></li>
                        @endforelse
                    </ul>

                    @if($reporte->otro)
                        <h6 class="mt-3 font-weight-bold">Observaciones Adicionales:</h6>
                        <p>{{ $reporte->otro }}</p>
                    @endif
                </div>

                <!-- Botones para imprimir -->
                <div class="text-center mt-4 no-print">
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimir Reporte
                    </button>
                    <a href="{{ route('reportes_conducta.por_alumno', $reporte->alumno_id) }}" class="btn btn-info">
                        <i class="fas fa-history"></i> Ver Historial del Alumno
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print { display: none !important; }
        .main-footer { display: none !important; }
        .content-wrapper { margin-left: 0 !important; background-color: white !important; }
    }
</style>
@stop
