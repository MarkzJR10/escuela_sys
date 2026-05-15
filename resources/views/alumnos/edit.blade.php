@extends('adminlte::page')

@section('title', 'Editar Alumno')

@section('content_header')
    <h1>Editar Alumno</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> ¡Atención!</h5>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('alumnos.update', $alumno) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Matrícula</label>
                            <input type="text" name="matricula" class="form-control" required value="{{ old('matricula', $alumno->matricula) }}" placeholder="Ej: 2024001">
                            @error('matricula') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $alumno->nombre) }}">
                            @error('nombre') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control" required value="{{ old('apellido_paterno', $alumno->apellido_paterno) }}">
                            @error('apellido_paterno') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Apellido Materno</label>
                            <input type="text" name="apellido_materno" class="form-control" value="{{ old('apellido_materno', $alumno->apellido_materno) }}">
                            @error('apellido_materno') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CURP</label>
                            <input type="text" name="curp" class="form-control" value="{{ old('curp', $alumno->curp) }}" placeholder="18 caracteres">
                            @error('curp') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento) }}">
                            @error('fecha_nacimiento') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Género</label>
                            <select name="genero" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <option value="M" {{ old('genero', $alumno->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('genero', $alumno->genero) == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                            @error('genero') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Grado y Grupo</label>
                            <select name="grado_grupo_id" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                @foreach($gradoGrupos as $gradoGrupo)
                                    <option value="{{ $gradoGrupo->id }}" {{ old('grado_grupo_id', $alumno->grado_grupo_id) == $gradoGrupo->id ? 'selected' : '' }}>
                                        {{ $gradoGrupo->grado }} {{ $gradoGrupo->grupo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('grado_grupo_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Colegiatura (Opcional)</label>
                            <input type="number" step="0.01" name="colegiatura" class="form-control" value="{{ old('colegiatura', $alumno->colegiatura) }}" placeholder="0.00">
                            @error('colegiatura') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $alumno->telefono) }}">
                            @error('telefono') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Celular</label>
                            <input type="text" name="celular" class="form-control" value="{{ old('celular', $alumno->celular) }}">
                            @error('celular') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Domicilio</label>
                    <textarea name="domicilio" class="form-control" rows="2">{{ old('domicilio', $alumno->domicilio) }}</textarea>
                    @error('domicilio') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Alergias</label>
                    <textarea name="alergias" class="form-control" rows="2">{{ old('alergias', $alumno->alergias) }}</textarea>
                    @error('alergias') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Padre de Familia (Opcional)</label>
                    <select name="padre_id" class="form-control select2">
                        <option value="">-- Sin asignar --</option>
                        @foreach($padres as $padre)
                            <option value="{{ $padre->id }}" {{ old('padre_id', $alumno->padre_id) == $padre->id ? 'selected' : '' }}>
                                {{ $padre->user->name }} ({{ $padre->curp }})
                            </option>
                        @endforeach
                    </select>
                    @error('padre_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Fotografía (Opcional)</label>
                    @if($alumno->fotografia)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $alumno->fotografia) }}" alt="Foto Alumno" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    @endif
                    <div class="input-group">
                        <div class="custom-file">
                            <input type="file" name="fotografia" class="custom-file-input" id="fotografia" accept="image/*">
                            <label class="custom-file-label" for="fotografia">Elegir archivo para cambiar...</label>
                        </div>
                    </div>
                    @error('fotografia') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                <a href="{{ route('alumnos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
@push('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: "-- Seleccione --",
            allowClear: true
        });

        // Para mostrar el nombre del archivo en el input custom-file
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@endpush
        </div>
    </div>
@stop
