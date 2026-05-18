@extends('adminlte::page')

@section('title', 'Cuadro de Honor')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary" style="font-size: 2rem;">
            🏆 Cuadro de Honor - {{ $selectedTrimestreId ? $selectedTrimestreId . 'er Trimestre' : '' }} <small class="text-muted" style="font-size: 1.2rem;">(Todos los Grados y Grupos, Top 5)</small>
        </h1>
        
        @if($isAdmin && $trimestres->count() > 0)
            <form action="{{ route('cuadro_honor.index') }}" method="GET" class="form-inline">
                <label for="trimestre" class="mr-2">Seleccionar Trimestre:</label>
                <select name="trimestre" id="trimestre" class="form-control mr-2" onchange="this.form.submit()">
                    @foreach($trimestres as $t)
                        <option value="{{ $t->trimestre }}" {{ $selectedTrimestreId == $t->trimestre ? 'selected' : '' }}>
                            Trimestre {{ $t->trimestre }} {{ !$t->activo ? '(Inactivo)' : '' }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>
@stop

@section('content')
    @if(!$selectedTrimestreId)
        <div class="alert alert-info">
            No hay trimestres activos disponibles para mostrar.
        </div>
    @elseif(empty($cuadroDeHonor))
        <div class="alert alert-warning">
            No hay calificaciones registradas para el trimestre seleccionado.
        </div>
    @else
        @foreach($cuadroDeHonor as $nombreGrupo => $alumnos)
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header text-white" style="background-color: #5b7cfa; border-radius: 5px 5px 0 0;">
                    <h3 class="card-title m-0" style="font-size: 1.4rem;">{{ $nombreGrupo }} 🏅</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped m-0">
                            <thead style="background-color: #495057; color: white;">
                                <tr>
                                    <th style="width: 8%">#</th>
                                    <th style="width: 15%">Matrícula</th>
                                    <th>Nombre</th>
                                    <th style="width: 15%">Promedio</th>
                                    <th style="width: 15%">Conducta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alumnos as $index => $alumno)
                                    <tr>
                                        <td>
                                            {{ $index + 1 }}
                                            @if($index == 0) 🥇
                                            @elseif($index == 1) 🥈
                                            @elseif($index == 2) 🥉
                                            @endif
                                        </td>
                                        <td>{{ $alumno->matricula }}</td>
                                        <td class="text-uppercase text-muted">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                                        <td>{{ number_format($alumno->promedio_calculado, 2) }}</td>
                                        <td>{{ number_format($alumno->conducta_calculada, 1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@stop

@section('css')
    <style>
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
@stop
