@extends('adminlte::page')

@section('title', 'Buscar Estado de Cuenta')

@section('content_header')
    <h1>Consulta de Estado de Cuenta</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Buscar Alumno por Nombre o Matrícula</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('reportes.estado_cuenta') }}" method="GET" class="mb-4">
                    <div class="input-group input-group-lg">
                        <input type="text" name="busqueda" class="form-control" placeholder="Escriba el nombre, apellidos o matrícula..." value="{{ request('busqueda') }}" required>
                        <span class="input-group-append">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
                        </span>
                    </div>
                </form>

                @if(request()->filled('busqueda'))
                    @if($alumnos->count() > 0)
                        <table class="table table-bordered table-striped mt-4">
                            <thead>
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Alumno</th>
                                    <th>Grado</th>
                                    <th>Padre/Tutor</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($alumnos as $alumno)
                                <tr>
                                    <td>{{ $alumno->matricula }}</td>
                                    <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                                    <td>{{ $alumno->gradoGrupo->grado ?? '' }} "{{ $alumno->gradoGrupo->grupo ?? '' }}"</td>
                                    <td>{{ $alumno->padre->nombre ?? 'N/A' }} {{ $alumno->padre->apellido_paterno ?? '' }}</td>
                                    <td>
                                        <a href="{{ route('reportes.detalle_alumno', $alumno->id) }}" class="btn btn-success">
                                            <i class="fas fa-file-invoice-dollar"></i> Ver Estado de Cuenta
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-warning mt-4">
                            No se encontraron alumnos que coincidan con la búsqueda.
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@stop
