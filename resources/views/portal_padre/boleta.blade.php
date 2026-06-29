@extends('adminlte::page')

@section('title', 'Boleta de Calificaciones')

@section('content_header')
    <h1>Boleta de Calificaciones</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} - {{ $alumno->gradoGrupo->grado ?? '' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"</h3>
                <div class="card-tools">
                    <a href="{{ route('boletas.pdf', $alumno->id) }}" class="btn btn-sm btn-danger" target="_blank">
                        <i class="fas fa-file-pdf"></i> Descargar PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($boletas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped text-center">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th class="text-left">Asignatura</th>
                                    <th>Trimestre 1</th>
                                    <th>Trimestre 2</th>
                                    <th>Trimestre 3</th>
                                    <th>Promedio Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boletas as $boleta)
                                <tr>
                                    <td class="text-left font-weight-bold">{{ $boleta->materia }}</td>
                                    <td>{{ $boleta->p1 ?? '-' }}</td>
                                    <td>{{ $boleta->p2 ?? '-' }}</td>
                                    <td>{{ $boleta->p3 ?? '-' }}</td>
                                    <td class="font-weight-bold bg-light">{{ $boleta->p_final ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">Aún no hay calificaciones registradas para este alumno.</div>
                @endif
                
                <div class="text-center mt-4">
                    <a href="{{ route('portal_padre.dashboard') }}" class="btn btn-default">Volver al Panel</a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
