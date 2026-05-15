@extends('adminlte::page')

@section('title', 'Editar Concepto SAT')

@section('content_header')
    <h1>Editar Concepto SAT</h1>
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

            <form action="{{ route('sat_conceptos.update', $satConcepto) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Clave SAT</label>
                    <input type="text" name="clave" class="form-control" required value="{{ old('clave', $satConcepto->clave) }}">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" class="form-control" required value="{{ old('descripcion', $satConcepto->descripcion) }}">
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select name="active" class="form-control">
                        <option value="1" {{ $satConcepto->active ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ !$satConcepto->active ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Actualizar</button>
                <a href="{{ route('sat_conceptos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop
