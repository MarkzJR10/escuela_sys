@extends('adminlte::page')

@section('title', 'Editar Colegiatura')

@section('content_header')
    <h1>Editar Colegiatura</h1>
@stop

@section('content')
    <div class="card col-md-8 offset-md-2">
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

            <form action="{{ route('colegiaturas-config.update', $colegiatura) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="nombre">Nombre de la Colegiatura</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" required value="{{ old('nombre', $colegiatura->nombre) }}">
                    @error('nombre') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="monto">Monto Mensual</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" name="monto" id="monto" class="form-control" required value="{{ old('monto', $colegiatura->monto) }}">
                    </div>
                    @error('monto') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                
                <div class="alert alert-info">
                    <h5><i class="icon fas fa-info"></i> Sincronización</h5>
                    Al cambiar el monto de esta colegiatura base, se actualizará automáticamente el costo mensual de los alumnos asociados para futuros periodos. Los adeudos ya generados anteriormente no se verán modificados para mantener la consistencia de la información histórica.
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
                </button>
                <a href="{{ route('colegiaturas-config.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </form>
        </div>
    </div>
@stop
