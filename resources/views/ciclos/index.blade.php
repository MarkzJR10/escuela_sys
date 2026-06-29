@extends('adminlte::page')

@section('title', 'Ciclos Masivo')

@section('content_header')
    <h1><i class="fas fa-calendar-alt text-primary"></i> Ciclos Masivo</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
    </div>
@endif

<div class="row">
    <div class="col-md-8 mx-auto">
        {{-- Selector de ciclo --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Seleccionar Ciclo Escolar</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('ciclos.index') }}" class="form-inline justify-content-center">
                    <div class="form-group mr-3">
                        <label for="ciclo" class="mr-2"><strong>Ciclo:</strong></label>
                        <select name="ciclo" id="ciclo" class="form-control">
                            <option value="">Seleccione...</option>
                            @foreach($opciones as $opcion)
                                <option value="{{ $opcion }}" {{ $cicloSeleccionado === $opcion ? 'selected' : '' }}>
                                    {{ $opcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </form>

                @if($cicloSeleccionado)
                    <hr>
                    <div class="text-center">
                        <p class="text-muted mb-2">
                            Ciclo vigente en configuración: <strong>{{ $cicloActual }}</strong>
                        </p>
                        <form method="POST" action="{{ route('ciclos.registrar_masivo') }}"
                              onsubmit="return confirm('¿Está seguro de registrar adeudos masivos para el ciclo {{ $cicloSeleccionado }}?\n\nSe crearán adeudos de colegiatura (Sep-Jun) para todos los alumnos activos que no los tengan aún.');">
                            @csrf
                            <input type="hidden" name="ciclo" value="{{ $cicloSeleccionado }}">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-plus-circle"></i> Registrar Ciclo {{ $cicloSeleccionado }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabla de adeudos del ciclo --}}
        @if($cicloSeleccionado)
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Adeudos del Ciclo {{ $cicloSeleccionado }}
                    <span class="badge badge-info ml-2">{{ $adeudos->count() }} registros</span>
                </h3>
            </div>
            <div class="card-body p-0">
                @if($adeudos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover mb-0" id="tblCiclos">
                            <thead class="bg-info text-white">
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Alumno</th>
                                    <th>Grado</th>
                                    <th>Periodo</th>
                                    <th class="text-right">Monto</th>
                                    <th class="text-center">Estatus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($adeudos as $adeudo)
                                <tr>
                                    <td>{{ $adeudo->alumno->matricula ?? 'N/A' }}</td>
                                    <td>
                                        {{ $adeudo->alumno->apellido_paterno ?? '' }}
                                        {{ $adeudo->alumno->apellido_materno ?? '' }}
                                        {{ $adeudo->alumno->nombre ?? '' }}
                                    </td>
                                    <td>{{ $adeudo->alumno->gradoGrupo->grado ?? '' }} "{{ $adeudo->alumno->gradoGrupo->grupo ?? '' }}"</td>
                                    <td>{{ $adeudo->periodo }}</td>
                                    <td class="text-right">${{ number_format($adeudo->monto_base, 2) }}</td>
                                    <td class="text-center">
                                        @if($adeudo->status === 'pagado')
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Pagado</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No hay adeudos registrados para el ciclo <strong>{{ $cicloSeleccionado }}</strong>.</p>
                        <p class="text-muted">Use el botón "Registrar Ciclo" para crear los adeudos masivos.</p>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(function() {
        if ($('#tblCiclos').length) {
            $('#tblCiclos').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
                },
                pageLength: 25,
                order: [[1, 'asc'], [3, 'asc']]
            });
        }
    });
</script>
@stop
