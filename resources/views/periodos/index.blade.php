@extends('adminlte::page')

@section('title', 'Control de Periodos')

@section('content_header')
    <h1>Control de Periodos de Calificaciones</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Configuración de Trimestres</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Enciende el interruptor para permitir la captura y edición de calificaciones en el trimestre correspondiente. Si está apagado, las calificaciones serán de solo lectura.</p>
            
            <div class="row mt-4">
                @foreach($periodos as $periodo)
                    <div class="col-md-4">
                        <div class="info-box shadow-sm">
                            <span class="info-box-icon bg-{{ $periodo->trimestre == 1 ? 'info' : ($periodo->trimestre == 2 ? 'warning' : 'success') }}">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text h5">Trimestre {{ $periodo->trimestre }}</span>
                                <div class="info-box-number">
                                    <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                        <input type="checkbox" class="custom-control-input toggle-periodo" 
                                               id="periodo_{{ $periodo->id }}" 
                                               data-id="{{ $periodo->id }}"
                                               {{ $periodo->activo ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="periodo_{{ $periodo->id }}">
                                            {{ $periodo->activo ? 'Abierto' : 'Cerrado' }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        $('.toggle-periodo').on('change', function() {
            const id = $(this).data('id');
            const label = $(this).next('label');
            
            $.ajax({
                url: `{{ url('periodos') }}/${id}/toggle`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        label.text(response.activo ? 'Abierto' : 'Cerrado');
                        if (window.toastr) toastr.success('Estado del periodo actualizado.');
                    }
                },
                error: function(xhr) {
                    console.error('Error AJAX:', xhr.responseText);
                    if (window.toastr) toastr.error('Error al actualizar el periodo.');
                }
            });
        });
    });
</script>
@endpush
