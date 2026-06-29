@extends('adminlte::page')

@section('title', 'Importar Pagos')

@section('content_header')
    <h1>Carga Masiva de Pagos (Excel)</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-6 mx-auto">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Subir Archivo de Pagos</h3>
            </div>
            <form action="{{ route('complementos.importar_pagos.post') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <p class="text-muted">El archivo debe contener las siguientes columnas (minúsculas): <code>matricula</code>, <code>monto</code>.</p>
                    <p class="text-info small">El sistema buscará el adeudo pendiente más antiguo del alumno y lo marcará como pagado.</p>
                    <div class="form-group">
                        <label for="archivo_excel">Archivo Excel (.xlsx, .csv)</label>
                        <input type="file" name="archivo_excel" id="archivo_excel" class="form-control-file" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Procesar Pagos</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
