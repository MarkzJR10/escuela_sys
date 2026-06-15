@extends('adminlte::page')

@section('title', 'Ver Boleta')

@section('content_header')
    <h1>Boleta de Calificaciones</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title">Detalle Académico</h3>
                <div class="card-tools">
                    <a href="{{ route('boletas.edit', $alumno->id) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h5><strong>Alumno:</strong> {{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</h5>
                        <p class="mb-0"><strong>Matrícula:</strong> {{ $alumno->matricula }}</p>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                        <h5><strong>Grado:</strong> {{ $alumno->gradoGrupo->grado ?? '' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"</h5>
                    </div>
                </div>

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
                    <div class="alert alert-info">No hay calificaciones registradas.</div>
                @endif
                
                <div class="text-center mt-4">
                    <a href="{{ route('boletas.index') }}" class="btn btn-default">Volver</a>
                    <!-- Botón PDF pendiente para cuando se integre DomPDF -->
                </div>
            </div>
        </div>
    </div>
</div>
@stop
