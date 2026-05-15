@extends('adminlte::page')

@section('title', 'Nuevo Concepto SAT')

@section('content_header')
    <h1>Nuevo Concepto SAT</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('sat_conceptos.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Clave SAT</label>
                    <input type="text" name="clave" class="form-control" required value="{{ old('clave') }}" placeholder="Ej: 86121501">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" class="form-control" required value="{{ old('descripcion') }}" placeholder="Ej: Prescolar">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="active" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="{{ route('sat_conceptos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
