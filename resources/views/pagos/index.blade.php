@extends('adminlte::page')

@section('title', 'Caja - Cobro de Alumnos')

@section('content_header')
    <h1>Caja (Buscador de Alumnos)</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Seleccione un alumno para realizar un cobro</h3>
            <div class="card-tools">
                <form action="{{ route('pagos.index') }}" method="GET" class="input-group input-group-sm" style="width: 300px;">
                    <input type="text" name="search" class="form-control float-right" placeholder="Nombre o Matrícula..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre Completo</th>
                        <th>Grado y Grupo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                        <tr>
                            <td><code>{{ $alumno->matricula }}</code></td>
                            <td>{{ $alumno->nombre }} {{ $alumno->apellidos }}</td>
                            <td>{{ $alumno->gradoGrupo->grado }} {{ $alumno->gradoGrupo->grupo }}</td>
                            <td class="text-center">
                                <a href="{{ route('pagos.create', $alumno->id) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-cash-register"></i> Realizar Cobro
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron alumnos con esos criterios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $alumnos->appends(request()->query())->links() }}
        </div>
    </div>
@stop
