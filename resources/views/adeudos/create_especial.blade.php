@extends('adminlte::page')

@section('title', 'Generar Adeudo Especial')

@section('content_header')
    <h1>Generar Adeudo Especial</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Selección de Destinatarios</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-check"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <i class="icon fas fa-ban"></i> {{ session('error') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('adeudos.store_especial') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="mb-0">Concepto del Adeudo</label>
                                @role('administrador')
                                    <a href="{{ route('conceptos_adeudos_especiales.index') }}" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-cog mr-1"></i> Administrar Conceptos
                                    </a>
                                @endrole
                            </div>
                            <select name="concepto" class="form-control" required {{ $conceptos->isEmpty() ? 'disabled' : '' }}>
                                <option value="">-- Seleccione un concepto --</option>
                                @foreach($conceptos as $c)
                                    <option value="{{ $c->nombre }}" {{ old('concepto') === $c->nombre ? 'selected' : '' }}>
                                        {{ $c->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @if($conceptos->isEmpty())
                                <small class="text-danger d-block mt-1">
                                    No hay conceptos de adeudos especiales activos.
                                    @role('administrador')
                                        <a href="{{ route('conceptos_adeudos_especiales.index') }}">Dar de alta conceptos aquí</a>.
                                    @endrole
                                </small>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>Monto</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" name="monto" class="form-control" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tipo de Destino</label>
                            <select name="tipo_destino" id="tipo_destino" class="form-control" onchange="toggleFiltros()">
                                <option value="individual">Individual (Un solo alumno)</option>
                                <option value="masivo">Masivo (Por Grupo / Género)</option>
                            </select>
                        </div>

                        <div id="filtro_individual" class="form-group">
                            <label>Alumnos (Captura nombre o matrícula e ir añandiendo...)</label>
                            <select name="alumno_ids[]" id="alumno_ids" class="form-control select2-ajax" multiple="multiple" data-placeholder="Buscar alumno por nombre o matrícula..." style="width: 100%;">
                                <!-- Los resultados se cargarán dinámicamente mediante AJAX -->
                            </select>
                            @error('alumno_ids') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div id="filtro_masivo" style="display:none;">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Grado</label>
                                        <select name="grado" class="form-control">
                                            <option value="">-- Todos --</option>
                                            @foreach($grados as $grado)
                                                <option value="{{ $grado }}">{{ $grado }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Grupo</label>
                                        <select name="grupo" class="form-control">
                                            <option value="">-- Todos --</option>
                                            @foreach($grupos as $grupo)
                                                <option value="{{ $grupo }}">{{ $grupo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-group">
                                        <label>Género</label>
                                        <select name="genero" class="form-control">
                                            <option value="todos">Todos</option>
                                            <option value="M">Masculino</option>
                                            <option value="F">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3">Generar Adeudo(s)</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> Ayuda</h5>
                <p>Usa esta pantalla para generar cobros extraordinarios que no son colegiaturas ordinarias.</p>
                <ul>
                    <li>Los adeudos especiales no generan recargos automáticos.</li>
                    <li>Si seleccionas masivo, el sistema buscará a todos los alumnos que cumplan con los filtros seleccionados (Grado, Grupo y Género).</li>
                </ul>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2-ajax').select2({
                ajax: {
                    url: "{{ route('adeudos.buscar_alumnos') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.results
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                language: {
                    inputTooShort: function () {
                        return "Por favor ingresa 2 o más caracteres";
                    },
                    noResults: function () {
                        return "No se encontraron alumnos";
                    },
                    searching: function () {
                        return "Buscando...";
                    }
                }
            });
        });

        function toggleFiltros() {
            var tipo = document.getElementById('tipo_destino').value;
            if (tipo === 'individual') {
                document.getElementById('filtro_individual').style.display = 'block';
                document.getElementById('filtro_masivo').style.display = 'none';
            } else {
                document.getElementById('filtro_individual').style.display = 'none';
                document.getElementById('filtro_masivo').style.display = 'block';
            }
        }
    </script>
@stop
