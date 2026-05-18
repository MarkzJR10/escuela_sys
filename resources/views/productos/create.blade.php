@extends('adminlte::page')

@section('title', 'Nuevo Producto')

@section('content_header')
    <h1>Nuevo Producto</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('productos.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control">{{ old('descripcion') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Precio</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" name="precio" class="form-control" value="{{ old('precio') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Clave SAT</label>
                            <input type="text" name="clave_sat" class="form-control" value="{{ old('clave_sat') }}">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="activo" name="activo" value="1" checked>
                                <label class="custom-control-label" for="activo">Activo (Disponible para venta)</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Guardar Producto</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
