@extends('adminlte::page')

@section('title', 'Conceptos de Adeudos Especiales')

@section('content_header')
    <h1>Catálogo de Conceptos de Adeudos Especiales</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="icon fas fa-check"></i> {{ session('success') }}
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

    <div class="row">
        {{-- Formulario de Alta --}}
        <div class="col-md-4">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-plus-circle mr-1 text-primary"></i> Dar de Alta Concepto
                    </h3>
                </div>
                <form action="{{ route('conceptos_adeudos_especiales.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nombre">Nombre del Concepto</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej: Vestuario, Viaje Escolar..." required>
                        </div>

                        <div class="form-group mb-0">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="checkbox" class="custom-control-input" id="activo_alta" name="activo" value="1" checked>
                                <label class="custom-control-label font-weight-bold" for="activo_alta">
                                    Estatus inicial: Encendido
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Solo los conceptos encendidos aparecerán en la pantalla de Cobros Especiales (<code>/adeudos/especial</code>).
                            </small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                            <i class="fas fa-save mr-1"></i> Guardar Concepto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla de Conceptos Registrados --}}
        <div class="col-md-8">
            <div class="card card-info card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-list mr-1 text-info"></i> Conceptos Registrados
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('adeudos.create_especial') }}" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-file-invoice mr-1"></i> Ir a Cobros Especiales
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;" class="text-center">#</th>
                                <th>Concepto</th>
                                <th style="width: 200px;" class="text-center">Estatus (Encendido / Apagado)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conceptos as $index => $concepto)
                                <tr>
                                    <td class="text-center align-middle">{{ $index + 1 }}</td>
                                    <td class="align-middle font-weight-bold">{{ $concepto->nombre }}</td>
                                    <td class="text-center align-middle">
                                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                            <input type="checkbox"
                                                   class="custom-control-input toggle-concepto"
                                                   id="concepto_{{ $concepto->id }}"
                                                   data-id="{{ $concepto->id }}"
                                                   {{ $concepto->activo ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold {{ $concepto->activo ? 'text-success' : 'text-danger' }}"
                                                   for="concepto_{{ $concepto->id }}">
                                                {{ $concepto->activo ? 'Encendido' : 'Apagado' }}
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        No hay conceptos de adeudos especiales dados de alta todavía.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#activo_alta').on('change', function() {
            var isChecked = $(this).is(':checked');
            $(this).next('label').text('Estatus inicial: ' + (isChecked ? 'Encendido' : 'Apagado'));
        });

        $('.toggle-concepto').on('change', function() {
            var checkbox = $(this);
            var id = checkbox.data('id');
            var label = checkbox.next('label');

            $.ajax({
                url: "{{ url('conceptos-adeudos-especiales') }}/" + id + "/toggle",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        if (response.activo) {
                            label.text('Encendido').removeClass('text-danger').addClass('text-success');
                        } else {
                            label.text('Apagado').removeClass('text-success').addClass('text-danger');
                        }
                    }
                },
                error: function() {
                    checkbox.prop('checked', !checkbox.is(':checked'));
                    alert('No se pudo actualizar el estatus del concepto.');
                }
            });
        });
    });
</script>
@stop

