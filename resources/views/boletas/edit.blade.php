@extends('adminlte::page')

@section('title', 'Capturar Calificaciones')

@section('content_header')
    <h1>Capturar Calificaciones (Boleta)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Alumno: {{ $alumno->nombre }} {{ $alumno->apellido_paterno }} ({{ $alumno->matricula }})</h3>
            </div>
            <div class="card-body">
                @if($boletas->count() > 0)
                    <form action="{{ route('boletas.update', $alumno->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <table class="table table-bordered table-sm text-center">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Materia</th>
                                    <th>1er Trimestre</th>
                                    <th>2do Trimestre</th>
                                    <th>3er Trimestre</th>
                                    <th>Promedio Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boletas as $boleta)
                                @php $isExtra = $boleta->is_extracurricular; @endphp
                                <tr>
                                    <td class="align-middle text-left font-weight-bold">
                                        {{ $boleta->materia }}
                                        @if($isExtra)
                                            <span class="badge badge-info ml-1">Extracurricular (A-D)</span>
                                        @endif
                                    </td>
                                    @foreach([1 => 'p1', 2 => 'p2', 3 => 'p3'] as $tNum => $pField)
                                    <td>
                                        @if($isExtra)
                                            @php $let = $boleta->letraTrimestre($tNum); @endphp
                                            <select name="boletas[{{ $boleta->id }}][{{ $pField }}]" class="form-control text-center font-weight-bold">
                                                <option value="">--</option>
                                                @foreach(['A', 'B', 'C', 'D'] as $opt)
                                                    <option value="{{ $opt }}" {{ $let === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="number" step="0.1" min="0" max="10" name="boletas[{{ $boleta->id }}][{{ $pField }}]" class="form-control text-center" value="{{ $boleta->{$pField} }}">
                                        @endif
                                    </td>
                                    @endforeach
                                    <td>
                                        @if($isExtra)
                                            <input type="text" class="form-control text-center bg-light" value="N/A (A-D)" disabled>
                                        @else
                                            <input type="number" step="0.1" min="0" max="10" name="boletas[{{ $boleta->id }}][p_final]" class="form-control text-center bg-light" value="{{ $boleta->p_final }}">
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="text-right mt-3">
                            <a href="{{ route('boletas.index') }}" class="btn btn-default">Cancelar</a>
                            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Guardar Calificaciones</button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning">
                        Este alumno no tiene materias en su boleta. <a href="{{ route('boletas.gestionar', $alumno->id) }}">Agregue materias primero</a>.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
