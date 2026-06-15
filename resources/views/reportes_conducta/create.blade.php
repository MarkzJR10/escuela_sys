@extends('adminlte::page')

@section('title', 'Capturar Reporte de Conducta')

@section('content_header')
    <h1>Nuevo Reporte de Conducta</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">Reporte #{{ $contadorReportes }} para {{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</h3>
            </div>
            
            <form action="{{ route('reportes_conducta.store') }}" method="POST">
                @csrf
                <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                
                <div class="card-body">
                    <div class="callout callout-info">
                        <h5>Datos del Alumno</h5>
                        <p>
                            <strong>Matrícula:</strong> {{ $alumno->matricula }} <br>
                            <strong>Grado y Grupo:</strong> {{ $alumno->gradoGrupo->grado ?? 'N/A' }} "{{ $alumno->gradoGrupo->grupo ?? 'N/A' }}"
                        </p>
                    </div>

                    <p class="text-muted font-weight-bold">Seleccione las razones del reporte:</p>

                    @php
                        $razones = [
                            'razon1' => 'Faltar el respeto al maestro.',
                            'razon2' => 'Molestar a sus compañeros.',
                            'razon3' => 'Pelear.',
                            'razon4' => 'Jugar dentro del aula.',
                            'razon5' => 'Utilizar lenguaje inadecuado.',
                            'razon6' => 'Hacer caso omiso de indicaciones.',
                            'razon7' => 'Incumplimiento de más de 3 tareas.',
                            'razon8' => 'No atender la clase por hacer tarea de otra materia.',
                            'razon9' => 'Indisciplina.',
                            'razon10' => 'Dañar las instalaciones, mobiliario y/o material escolar.',
                            'razon11' => 'Presentar un promedio semanal de conducta menor de 7.'
                        ];
                    @endphp

                    @foreach($razones as $campo => $texto)
                    <div class="form-group custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" id="{{ $campo }}" name="{{ $campo }}" value="1">
                        <label for="{{ $campo }}" class="custom-control-label font-weight-normal">{{ $texto }}</label>
                    </div>
                    @endforeach

                    <div class="form-group mt-4">
                        <label for="otro">Observaciones adicionales (Opcional):</label>
                        <textarea name="otro" id="otro" class="form-control" rows="3" placeholder="Detalles específicos..."></textarea>
                    </div>
                </div>

                <div class="card-footer">
                    <a href="{{ route('reportes_conducta.seleccionar') }}" class="btn btn-default">Cancelar</a>
                    <button type="submit" class="btn btn-danger float-right"><i class="fas fa-save"></i> Guardar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
