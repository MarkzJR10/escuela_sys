@extends('adminlte::page')

@section('title', 'Asignar Maestro a Materia')

@section('content_header')
    <h1>Asignación Maestro - Materia</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="icon fas fa-check"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="icon fas fa-ban"></i> {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="icon fas fa-exclamation-triangle"></i> Por favor corrige los errores:
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Nueva Asignación</h3>
            </div>
            <form action="{{ route('maestro_materia.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="profesor_id">Profesor</label>
                        <select name="profesor_id" id="profesor_id" class="form-control" required>
                            <option value="">Seleccione profesor...</option>
                            @foreach($profesores as $p)
                                <option value="{{ $p->id }}" {{ old('profesor_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="materia_id">Materia</label>
                        <select name="materia_id" id="materia_id" class="form-control" required>
                            <option value="">Seleccione materia...</option>
                            @foreach($materias as $m)
                                <option value="{{ $m->id }}" data-grado="{{ $m->grado }}" {{ old('materia_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->nombre_con_grado }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Se muestra como: <strong>Materia - Grado</strong>.</small>
                    </div>

                    <div class="form-group">
                        <label for="grado_grupo_id">Grado/Grupo (Opcional)</label>
                        <select name="grado_grupo_id" id="grado_grupo_id" class="form-control">
                            <option value="">-- Primero seleccione una materia --</option>
                            @foreach($gradoGrupos as $g)
                                <option value="{{ $g->id }}" data-grado="{{ $g->grado }}" {{ old('grado_grupo_id') == $g->id ? 'selected' : '' }}>
                                    {{ $g->grado }} "{{ $g->grupo }}"
                                </option>
                            @endforeach
                        </select>
                        <small id="grado_grupo_help" class="text-muted">Deje en blanco si el maestro imparte esta materia a todos los grupos de este grado.</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Asignaciones Actuales</h3>
            </div>
            <div class="card-body p-0">
                <table id="maestro-materia-table" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Profesor</th>
                            <th>Materia - Grado</th>
                            <th>Grupo Asignado</th>
                            <th width="100px" class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($asignaciones as $a)
                            <tr>
                                <td class="align-middle font-weight-bold">{{ $a->profesor_completo }}</td>
                                <td class="align-middle">
                                    {{ $a->materia_nombre }}{{ $a->materia_grado ? ' - ' . $a->materia_grado : '' }}
                                </td>
                                <td class="align-middle">
                                    @if($a->grado)
                                        <span class="badge badge-primary px-2 py-1 font-weight-bold" style="font-size: 0.9rem;">
                                            {{ $a->grado }} "{{ $a->grupo }}"
                                        </span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1">
                                            <i class="fas fa-layer-group mr-1"></i> Todos los grupos
                                        </span>
                                    @endif
                                </td>
                                <td class="align-middle text-center">
                                    <form action="{{ route('maestro_materia.destroy', $a->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger shadow-sm" onclick="return confirm('¿Eliminar esta asignación?')" title="Eliminar asignación">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay asignaciones registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#maestro-materia-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": 3 }
            ]
        });

        // Almacenar todos los grupos en memoria para el filtrado dinámico
        var allGrupos = [];
        $('#grado_grupo_id option').each(function() {
            var val = $(this).val();
            if (val !== "") {
                allGrupos.push({
                    value: val,
                    text: $(this).text().trim(),
                    grado: String($(this).data('grado') || '').trim()
                });
            }
        });

        var initialSelectedGrupo = "{{ old('grado_grupo_id') }}";

        function filtrarGruposPorMateria(preserveSelection) {
            var $materiaSelect = $('#materia_id');
            var $grupoSelect = $('#grado_grupo_id');
            var $helpText = $('#grado_grupo_help');
            
            var selectedOption = $materiaSelect.find('option:selected');
            var materiaId = $materiaSelect.val();
            var materiaGrado = selectedOption.data('grado');
            
            var valorASeleccionar = preserveSelection ? (initialSelectedGrupo || $grupoSelect.val()) : '';

            $grupoSelect.empty();

            if (!materiaId) {
                $grupoSelect.append('<option value="">-- Primero seleccione una materia --</option>');
                $grupoSelect.prop('disabled', true);
                $helpText.text('Seleccione primero una materia para ver los grupos disponibles.');
                return;
            }

            $grupoSelect.prop('disabled', false);

            if (materiaGrado !== undefined && materiaGrado !== null && String(materiaGrado).trim() !== '') {
                var gradoStr = String(materiaGrado).trim();
                $grupoSelect.append('<option value="">Aplica a todos los grupos del grado ' + gradoStr + '...</option>');
                $helpText.text('Deje en blanco si el maestro imparte esta materia a todos los grupos de ' + gradoStr + '°.');

                var matchingGrupos = allGrupos.filter(function(g) {
                    return g.grado === gradoStr;
                });

                if (matchingGrupos.length > 0) {
                    $.each(matchingGrupos, function(i, g) {
                        var isSelected = (String(g.value) === String(valorASeleccionar)) ? ' selected' : '';
                        $grupoSelect.append('<option value="' + g.value + '" data-grado="' + g.grado + '"' + isSelected + '>' + g.text + '</option>');
                    });
                } else {
                    $grupoSelect.append('<option value="" disabled>No hay grupos registrados para el grado ' + gradoStr + '</option>');
                }
            } else {
                // Materia general o sin grado especificado
                $grupoSelect.append('<option value="">Aplica a todos los grados y grupos...</option>');
                $helpText.text('Deje en blanco si aplica a todos los grupos.');
                $.each(allGrupos, function(i, g) {
                    var isSelected = (String(g.value) === String(valorASeleccionar)) ? ' selected' : '';
                    $grupoSelect.append('<option value="' + g.value + '" data-grado="' + g.grado + '"' + isSelected + '>' + g.text + '</option>');
                });
            }
        }

        // Ejecutar al cargar la página (respetando old input en caso de validación)
        filtrarGruposPorMateria(true);

        // Ejecutar cuando se seleccione otra materia
        $('#materia_id').on('change', function() {
            initialSelectedGrupo = '';
            filtrarGruposPorMateria(false);
        });
    });
</script>
@stop
