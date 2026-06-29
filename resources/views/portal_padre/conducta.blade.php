@extends('adminlte::page')

@section('title', 'Conducta')

@section('content_header')
    <h1>Reportes de Conducta</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</h3>
            </div>
            <div class="card-body">
                @if($reportes->count() > 0)
                    <div class="timeline">
                        @foreach($reportes as $reporte)
                            <div>
                                <i class="fas fa-exclamation-triangle bg-warning"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $reporte->fecha->format('d/m/Y') }}</span>
                                    <h3 class="timeline-header">
                                        Reporte #{{ $reporte->no_reporte }} por <strong>{{ $reporte->usuario->name }}</strong>
                                    </h3>

                                    <div class="timeline-body">
                                        <ul>
                                            @foreach($reporte->razones_marcadas as $razon)
                                                <li>{{ $razon }}</li>
                                            @endforeach
                                        </ul>
                                        @if($reporte->otro)
                                            <p class="mt-2 text-muted">{{ $reporte->otro }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> El alumno no tiene reportes de conducta. ¡Excelente!
                    </div>
                @endif
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('portal_padre.dashboard') }}" class="btn btn-default">Volver al Panel</a>
            </div>
        </div>
    </div>
</div>
@stop
