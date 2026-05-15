@extends('adminlte::page')

@section('title', 'Editar Materia')

@section('content_header')
    <h1>Editar Materia</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('materias.update', $materia) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Nombre de la materia</label>
                    <input type="text" name="nombre" class="form-control" required value="{{ old('nombre', $materia->nombre) }}">
                    @error('nombre') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Grado</label>
                    <select name="grado" class="form-control" required>
                        <option value="">-- Seleccione --</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado }}" {{ old('grado', $materia->grado) == $grado ? 'selected' : '' }}>
                                {{ $grado }}
                            </option>
                        @endforeach
                    </select>
                    @error('grado') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-success">Actualizar</button>
                <a href="{{ route('materias.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
