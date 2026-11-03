@extends('adminlte::page')

@section('title', 'Editar Calificación')

@section('content_header')
    <h1>Editar Calificación #{{ $calificacione->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('calificaciones.update', $calificacione) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Alumno</label>
                    <select name="alumno_id" class="form-control select2" required disabled>
                        @foreach($alumnos as $alumno)
                            <option value="{{ $alumno->id }}" {{ $calificacione->alumno_id == $alumno->id ? 'selected' : '' }}>
                                {{ $alumno->nombre }} {{ $alumno->apellidos }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Hidden input because disabled select isn't submitted --}}
                    <input type="hidden" name="alumno_id" value="{{ $calificacione->alumno_id }}">
                </div>

                <div class="form-group">
                    <label>Materia</label>
                    <select name="materia_id" id="materia_id_select" class="form-control" required>
                        @foreach($materias as $materia)
                            <option value="{{ $materia->id }}" data-extra="{{ $materia->is_extracurricular ? '1' : '0' }}" {{ $calificacione->materia_id == $materia->id ? 'selected' : '' }}>
                                {{ $materia->nombre }} ({{ $materia->grado }})
                            </option>
                        @endforeach
                    </select>
                    @error('materia_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Trimestre</label>
                    <select name="trimestre" class="form-control" required>
                        <option value="1" {{ old('trimestre', $calificacione->trimestre) == 1 ? 'selected' : '' }}>1° Trimestre</option>
                        <option value="2" {{ old('trimestre', $calificacione->trimestre) == 2 ? 'selected' : '' }}>2° Trimestre</option>
                        <option value="3" {{ old('trimestre', $calificacione->trimestre) == 3 ? 'selected' : '' }}>3° Trimestre</option>
                    </select>
                    @error('trimestre') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                @php
                    $isCurrentExtra = $calificacione->materia && $calificacione->materia->is_extracurricular;
                    $currentLetter = $calificacione->letra_extracurricular;
                @endphp

                <div class="form-group" id="group_puntaje_num" style="{{ $isCurrentExtra ? 'display:none;' : '' }}">
                    <label>Puntaje (0 - 10)</label>
                    <input type="number" step="0.1" name="puntaje" id="input_puntaje_num" class="form-control" min="0" max="10" {{ $isCurrentExtra ? 'disabled' : 'required' }} value="{{ old('puntaje', $calificacione->puntaje) }}">
                    @error('puntaje') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="form-group" id="group_puntaje_extra" style="{{ $isCurrentExtra ? '' : 'display:none;' }}">
                    <label>Calificación Extracurricular (A, B, C, D)</label>
                    <select name="puntaje" id="select_puntaje_extra" class="form-control font-weight-bold" {{ $isCurrentExtra ? 'required' : 'disabled' }}>
                        <option value="A" {{ old('puntaje', $currentLetter) === 'A' ? 'selected' : '' }}>A - Excellent</option>
                        <option value="B" {{ old('puntaje', $currentLetter) === 'B' ? 'selected' : '' }}>B - Satisfactory</option>
                        <option value="C" {{ old('puntaje', $currentLetter) === 'C' ? 'selected' : '' }}>C - Average</option>
                        <option value="D" {{ old('puntaje', $currentLetter) === 'D' ? 'selected' : '' }}>D - Unsatisfactory</option>
                    </select>
                    @error('puntaje') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Actualizar Calificación</button>
                    <a href="{{ route('calificaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            if($('.select2').length > 0) {
                $('.select2').select2();
            }

            function toggleExtraInput() {
                var isExtra = $('#materia_id_select option:selected').data('extra') == '1';
                if (isExtra) {
                    $('#group_puntaje_num').hide();
                    $('#input_puntaje_num').prop('disabled', true).prop('required', false);
                    $('#group_puntaje_extra').show();
                    $('#select_puntaje_extra').prop('disabled', false).prop('required', true);
                } else {
                    $('#group_puntaje_extra').hide();
                    $('#select_puntaje_extra').prop('disabled', true).prop('required', false);
                    $('#group_puntaje_num').show();
                    $('#input_puntaje_num').prop('disabled', false).prop('required', true);
                }
            }

            $('#materia_id_select').on('change', toggleExtraInput);
            toggleExtraInput();
        });
    </script>
@stop
