@extends('adminlte::page')

@section('title', 'Recargos y Adeudos Manual')

@section('content_header')
    <h1>Ejecución Manual de Recargos</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">Ejecutar Proceso de Adeudos</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('command_output'))
                        <div class="alert alert-info">
                            <h5>Resultados de la Ejecución:</h5>
                            <pre class="bg-dark text-white p-3 rounded" style="white-space: pre-wrap;">{{ session('command_output') }}</pre>
                        </div>
                    @endif

                    <form action="{{ route('adeudos.ejecutar_recargos_manual') }}" method="POST" onsubmit="return confirm('¿Estás seguro de ejecutar este proceso? Esta acción modificará los montos de los adeudos de los alumnos en la base de datos y no se puede deshacer.');">
                        @csrf
                        <div class="form-group">
                            <label for="dia">Día a procesar</label>
                            <select name="dia" id="dia" class="form-control" required>
                                <option value="1">Día 1 (Aplica recargos a meses anteriores y activa mes actual)</option>
                                <option value="11">Día 11 (Aplica primer recargo al mes actual)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-warning mt-3">
                            <i class="fas fa-play"></i> Ejecutar Proceso Manualmente
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> Información Importante</h5>
                <p>El sistema ejecuta este proceso automáticamente los días <strong>1 y 11</strong> de cada mes a la medianoche.</p>
                <p>Solo debes utilizar esta pantalla si:</p>
                <ul>
                    <li>El proceso automático falló por alguna razón técnica.</li>
                    <li>Estás realizando pruebas o ajustes en los periodos.</li>
                </ul>
                <p><strong>Día 1:</strong> Revisa todos los adeudos vencidos de meses anteriores y les aplica un recargo acumulativo del 10%. Además, toma las colegiaturas "programadas" del mes actual y las pasa a estado "pendiente".</p>
                <p><strong>Día 11:</strong> Revisa todas las colegiaturas del mes actual que sigan en estado "pendiente" y las pasa a estado "vencido", aplicándoles su primer recargo del 10%.</p>
            </div>
        </div>
    </div>
@stop
