@extends('adminlte::page')

@section('title', 'Asistencia de Maestros')

@section('content_header')
    <h1>Registro de Asistencia de Profesores</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card card-info">
            <div class="card-header">
                <form action="{{ route('asistencia_maestros.index') }}" method="GET" class="form-inline">
                    <label for="fecha" class="mr-2">Fecha a registrar:</label>
                    <input type="date" name="fecha" id="fecha" class="form-control mr-2" value="{{ $fecha }}">
                    <button type="submit" class="btn btn-dark"><i class="fas fa-calendar-day"></i> Cargar Fecha</button>
                </form>
            </div>
            
            <form action="{{ route('asistencia_maestros.store') }}" method="POST">
                @csrf
                <input type="hidden" name="fecha" value="{{ $fecha }}">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Profesor</th>
                                <th>Estado</th>
                                <th>Hora Entrada</th>
                                <th>Hora Salida</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profesores as $p)
                                @php
                                    $asist = $asistencias->get($p->id);
                                    $estado = $asist ? $asist->estado : 'presente';
                                @endphp
                                <tr>
                                    <td class="align-middle font-weight-bold">{{ $p->apellido_paterno }} {{ $p->nombre }}</td>
                                    <td>
                                        <select name="asistencias[{{ $p->id }}][estado]" class="form-control form-control-sm" required>
                                            <option value="presente" {{ $estado == 'presente' ? 'selected' : '' }}>Presente</option>
                                            <option value="falta" {{ $estado == 'falta' ? 'selected' : '' }}>Falta</option>
                                            <option value="retardo" {{ $estado == 'retardo' ? 'selected' : '' }}>Retardo</option>
                                            <option value="justificado" {{ $estado == 'justificado' ? 'selected' : '' }}>Justificado</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="time" name="asistencias[{{ $p->id }}][hora_entrada]" class="form-control form-control-sm" value="{{ $asist && $asist->hora_entrada ? substr($asist->hora_entrada, 0, 5) : '' }}">
                                    </td>
                                    <td>
                                        <input type="time" name="asistencias[{{ $p->id }}][hora_salida]" class="form-control form-control-sm" value="{{ $asist && $asist->hora_salida ? substr($asist->hora_salida, 0, 5) : '' }}">
                                    </td>
                                    <td>
                                        <input type="text" name="asistencias[{{ $p->id }}][observaciones]" class="form-control form-control-sm" value="{{ $asist->observaciones ?? '' }}" placeholder="Opcional">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-info btn-lg"><i class="fas fa-save"></i> Guardar Asistencias del Día</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
