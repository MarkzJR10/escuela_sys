@extends('adminlte::page')

@section('title', 'Alumnos Destacados')

@section('content_header')
    <h1>Conducta Destacada</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <form action="{{ route('conducta_destacada') }}" method="GET" class="form-inline">
                    <label for="mes" class="mr-2">Ver Mes:</label>
                    <input type="month" name="mes" id="mes" class="form-control mr-2" value="{{ $mes }}">
                    <button type="submit" class="btn btn-dark"><i class="fas fa-search"></i> Consultar</button>
                    <button type="button" class="btn btn-secondary ml-auto" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
                </form>
            </div>
            
            <div class="card-body">
                <div class="alert alert-info text-center">
                    <i class="fas fa-star text-warning"></i> Alumnos sin reportes de conducta durante <strong>{{ \Carbon\Carbon::parse($mes.'-01')->translatedFormat('F Y') }}</strong> <i class="fas fa-star text-warning"></i>
                </div>

                @if($destacados->count() > 0)
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="bg-warning">
                            <tr>
                                <th>Alumno</th>
                                <th>Grado y Grupo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($destacados as $alumno)
                                <tr>
                                    <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                                    <td>{{ $alumno->gradoGrupo->grado ?? '' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-warning">
                        Todos los alumnos tienen reportes en este mes.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print, .main-sidebar, .main-header, form { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>
@stop
