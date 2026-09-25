@extends('adminlte::page')

@section('title', 'Importar Pagos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Carga Masiva de Pagos (Excel / CSV)</h1>
        <a href="{{ route('complementos.importar_pagos.ejemplo') }}" class="btn btn-success">
            <i class="fas fa-file-download mr-1"></i> Descargar Ejemplo (.csv)
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
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

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-import mr-1"></i> Subir Archivo de Pagos</h3>
            </div>
            <form action="{{ route('complementos.importar_pagos.post') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="callout callout-info mb-4">
                        <h5><i class="fas fa-info-circle mr-1"></i> Instrucciones de Carga:</h5>
                        <p class="mb-2">1. Puedes descargar la plantilla de archivo llenada previamente dando clic en el botón <strong>"Descargar Ejemplo"</strong>.</p>
                        <p class="mb-2">2. El archivo (en formato <code>.xlsx</code>, <code>.xls</code> o <code>.csv</code>) debe contener obligatoriamente los siguientes encabezados en la primera fila:</p>
                        <ul class="mb-2">
                            <li><code>matricula</code>: Matrícula exacta del alumno registrado.</li>
                            <li><code>monto</code>: Monto o cantidad económica del pago (ejemplo: <code>500.00</code>).</li>
                        </ul>
                        <p class="mb-0 text-muted"><i class="fas fa-magic mr-1"></i> <em>El sistema buscará el adeudo pendiente más antiguo del alumno y aplicará el pago automáticamente.</em></p>
                    </div>

                    <div class="form-group">
                        <label for="archivo_excel">Archivo Excel o CSV</label>
                        <input type="file" name="archivo_excel" id="archivo_excel" class="form-control-file" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload mr-1"></i> Procesar Pagos</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
