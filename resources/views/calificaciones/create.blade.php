@extends('adminlte::page')

@section('title', 'Asignar Calificación')

@section('content_header')
    <h1>Asignar Nueva Calificación</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('calificaciones.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Alumno</label>
                    <select name="alumno_id" class="form-control select2" required>
                        <option value="">-- Seleccione Alumno --</option>
                        @foreach($alumnos as $alumno)
                            <option value="{{ $alumno->id }}" {{ old('alumno_id') == $alumno->id ? 'selected' : '' }}>
                                {{ $alumno->nombre }} {{ $alumno->apellidos }}
                            </option>
                        @endforeach
                    </select>
                    @error('alumno_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Materia</label>
                    <select name="materia_id" class="form-control" required>
                        <option value="">-- Seleccione Materia --</option>
                        @foreach($materias as $materia)
                            <option value="{{ $materia->id }}" {{ old('materia_id') == $materia->id ? 'selected' : '' }}>
                                {{ $materia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('materia_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Periodo (ej. Trimestre 1, Bloque 2)</label>
                    <input type="text" name="periodo" class="form-control" placeholder="Ej. Trimestre 1" required value="{{ old('periodo') }}">
                    @error('periodo') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Puntaje (0 - 100)</label>
                    <input type="number" step="0.01" name="puntaje" class="form-control" min="0" max="100" required value="{{ old('puntaje') }}">
                    @error('puntaje') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Guardar Calificación</button>
                    <a href="{{ route('calificaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Si tienes select2 instalado en AdminLTE, esto lo hará más bonito
            if($('.select2').length > 0) {
                $('.select2').select2();
            }
        });
    </script>
@stop
