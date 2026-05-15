@extends('adminlte::page')

@section('title', 'Editar Padre de Familia')

@section('content_header')
    <h1>Editar Padre de Familia</h1>
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

            <form action="{{ route('padres.update', $padre) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nombre(s)</label>
                            <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $padre->nombre) }}">
                            @error('nombre') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" class="form-control" required value="{{ old('apellido_paterno', $padre->apellido_paterno) }}">
                            @error('apellido_paterno') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Apellido Materno</label>
                            <input type="text" name="apellido_materno" class="form-control" value="{{ old('apellido_materno', $padre->apellido_materno) }}">
                            @error('apellido_materno') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>CURP</label>
                            <input type="text" name="curp" class="form-control" value="{{ old('curp', $padre->curp) }}" placeholder="18 caracteres">
                            @error('curp') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha de Nacimiento</label>
                            <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento', $padre->fecha_nacimiento) }}">
                            @error('fecha_nacimiento') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Género</label>
                            <select name="genero" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <option value="M" {{ old('genero', $padre->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('genero', $padre->genero) == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                            @error('genero') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Email (Cuenta de usuario vinculada)</label>
                            <input type="email" name="email" class="form-control" required value="{{ old('email', $padre->user ? $padre->user->email : '') }}" placeholder="correo@ejemplo.com">
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cambiar Password (Opcional)</label>
                            <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Teléfono Fijo</label>
                            <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $padre->telefono) }}">
                            @error('telefono') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Celular</label>
                            <input type="text" name="celular" class="form-control" value="{{ old('celular', $padre->celular) }}">
                            @error('celular') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Domicilio</label>
                    <textarea name="domicilio" class="form-control" rows="2">{{ old('domicilio', $padre->domicilio) }}</textarea>
                    @error('domicilio') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Fotografía (Opcional)</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="fotografia" class="custom-file-input" id="fotografia" accept="image/*">
                                    <label class="custom-file-label" for="fotografia">Elegir archivo</label>
                                </div>
                            </div>
                            @error('fotografia') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        @if($padre->fotografia)
                            <div class="text-center">
                                <img src="{{ asset('storage/' . $padre->fotografia) }}" alt="Foto" class="img-thumbnail" style="height: 100px;">
                                <p class="small">Foto actual</p>
                            </div>
                        @else
                            <div class="text-center">
                                <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" alt="Sin Foto" class="img-thumbnail" style="height: 100px;">
                                <p class="small text-muted">Sin fotografía</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Actualizar Padre</button>
                    <a href="{{ route('padres.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Para mostrar el nombre del archivo en el input custom-file
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    });
</script>
@stop
