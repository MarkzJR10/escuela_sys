@extends('adminlte::page')

@section('title', 'Nueva Colegiatura')

@section('content_header')
    <h1>Nueva Colegiatura</h1>
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

            <form action="{{ route('colegiaturas-config.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nombre">Nombre de la Colegiatura</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ej: Preescolar" required value="{{ old('nombre') }}">
                    @error('nombre') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="monto">Monto Mensual</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">$</span>
                        </div>
                        <input type="number" step="0.01" name="monto" id="monto" class="form-control" placeholder="Ej: 1500.00" required value="{{ old('monto') }}">
                    </div>
                    @error('monto') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="{{ route('colegiaturas-config.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </form>
        </div>
    </div>
@stop
