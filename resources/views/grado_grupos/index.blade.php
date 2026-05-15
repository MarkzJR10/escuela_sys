@extends('adminlte::page')

@section('title', 'Grados y Grupos')

@section('content_header')
    <h1>Lista de Grados y Grupos</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('grado_grupos.create') }}" class="btn btn-primary">Nuevo Grado/Grupo</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Grado</th>
                        <th>Grupo</th>
                        <th>Alumnos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gradoGrupos as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->grado }}</td>
                        <td>{{ $item->grupo }}</td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm view-students" 
                                    data-grado="{{ $item->grado }}" 
                                    data-grupo="{{ $item->grupo }}"
                                    data-alumnos="{{ json_encode($item->alumnos) }}">
                                <i class="fas fa-users"></i> {{ $item->alumnos_count }} Alumnos
                            </button>
                        </td>
                        <td>
                            <a href="{{ route('grado_grupos.edit', $item) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('grado_grupos.destroy', $item) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este grado y grupo?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para ver alumnos -->
    <div class="modal fade" id="modalAlumnos" tabindex="-1" role="dialog" aria-labelledby="modalAlumnosLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="modalAlumnosLabel">
                        <i class="fas fa-users"></i> Alumnos - <span id="modalTitleGroup"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Matrícula</th>
                                <th>Nombre Completo</th>
                                <th class="text-center">Género</th>
                            </tr>
                        </thead>
                        <tbody id="alumnosTableBody">
                            <!-- Se llena con JS -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.view-students').on('click', function() {
            const grado = $(this).data('grado');
            const grupo = $(this).data('grupo');
            const alumnos = $(this).data('alumnos');

            $('#modalTitleGroup').text(`${grado} ${grupo}`);
            let rows = '';

            if (alumnos.length === 0) {
                rows = '<tr><td colspan="3" class="text-center text-muted">No hay alumnos asignados a este grupo.</td></tr>';
            } else {
                alumnos.forEach(alumno => {
                    const generoIcon = alumno.genero === 'M' 
                        ? '<i class="fas fa-mars text-primary"></i> M' 
                        : (alumno.genero === 'F' ? '<i class="fas fa-venus text-danger"></i> F' : '---');
                    
                    rows += `
                        <tr>
                            <td><code>${alumno.matricula || '---'}</code></td>
                            <td>${alumno.nombre} ${alumno.apellidos}</td>
                            <td class="text-center">${generoIcon}</td>
                        </tr>
                    `;
                });
            }

            $('#alumnosTableBody').html(rows);
            $('#modalAlumnos').modal('show');
        });
    });
</script>
@stop
