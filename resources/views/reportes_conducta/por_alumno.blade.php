@extends('adminlte::page')

@section('title', 'Historial de Reportes')

@section('content_header')
    <h1>Historial de Conducta</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        <!-- Perfil del Alumno -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @if($alumno->fotografia)
                        <img class="profile-user-img img-fluid img-circle" src="{{ asset('storage/'.$alumno->fotografia) }}" alt="Foto">
                    @else
                        <img class="profile-user-img img-fluid img-circle" src="{{ asset('vendor/adminlte/dist/img/avatar.png') }}" alt="User">
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $alumno->nombre }}</h3>
                <p class="text-muted text-center">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Matrícula</b> <a class="float-right">{{ $alumno->matricula }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Grado</b> <a class="float-right">{{ $alumno->gradoGrupo->grado ?? 'N/A' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"</a>
                    </li>
                    <li class="list-group-item">
                        <b>Total Reportes</b> <a class="float-right text-danger font-weight-bold">{{ $reportes->count() }}</a>
                    </li>
                </ul>
                <a href="{{ route('reportes_conducta.create', ['alumno_id' => $alumno->id]) }}" class="btn btn-danger btn-block"><b>Generar Nuevo Reporte</b></a>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="card-title">Línea de Tiempo de Reportes</h3>
            </div>
            <div class="card-body">
                @if($reportes->count() > 0)
                    <div class="timeline">
                        @foreach($reportes as $reporte)
                            <div>
                                <i class="fas fa-exclamation-triangle bg-danger"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $reporte->fecha->format('d/m/Y') }}</span>
                                    <h3 class="timeline-header">
                                        <a href="{{ route('reportes_conducta.show', $reporte->id) }}">Reporte #{{ $reporte->no_reporte }}</a> 
                                        reportado por <strong>{{ $reporte->usuario->name }}</strong>
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
                                    <div class="timeline-footer">
                                        <a href="{{ route('reportes_conducta.show', $reporte->id) }}" class="btn btn-primary btn-sm">Leer más</a>
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
                        El alumno no tiene reportes de conducta registrados.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
