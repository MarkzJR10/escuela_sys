@extends('adminlte::page')

@section('title', 'Padres de Familia')

@section('content_header')
    <h1>Padres de Familia</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalRegistro">
                <i class="fas fa-plus"></i> Nuevo Registro
            </button>
            
            <div class="card-tools">
                <form action="{{ route('padres.index') }}" method="GET" class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="search" class="form-control float-right" placeholder="Buscar..." value="{{ $search }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="icon fas fa-check"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre Completo</th>
                        <th>CURP / Usuario</th>
                        <th>Contacto</th>
                        <th>Hijos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($padres as $padre)
                    <tr>
                        <td class="text-center">
                            @if($padre->fotografia)
                                <img src="{{ asset('storage/' . $padre->fotografia) }}" class="img-circle elevation-2" style="height: 40px; width: 40px; object-fit: cover;">
                            @else
                                <img src="{{ asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" style="height: 40px;">
                            @endif
                        </td>
                        <td>
                            {{ $padre->nombre }} {{ $padre->apellido_paterno }} {{ $padre->apellido_materno }}
                        </td>
                        <td>
                            <code>{{ $padre->curp }}</code><br>
                            <small class="text-muted">{{ $padre->user->email }}</small>
                        </td>
                        <td>
                            <i class="fas fa-phone"></i> {{ $padre->telefono }}<br>
                            @if($padre->celular)
                                <i class="fas fa-mobile-alt"></i> {{ $padre->celular }}
                            @endif
                        </td>
                        <td>
                            @if($padre->alumnos->count() > 0)
                                <button class="btn btn-info btn-sm btn-hijos" data-id="{{ $padre->id }}" data-count="{{ $padre->alumnos->count() }}">
                                    <i class="fas fa-child"></i> ({{ $padre->alumnos->count() }})
                                </button>
                            @else
                                <span class="badge badge-secondary">Sin hijos</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('padres.edit', $padre) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-success btn-sm btn-facturacion" 
                                        data-id="{{ $padre->id }}" 
                                        data-url="{{ route('padres.billing', $padre) }}"
                                        data-json="{{ json_encode($padre->datosFacturacion) }}"
                                        title="Facturación">
                                    <i class="fas fa-file-invoice"></i>
                                </button>
                                <form action="{{ route('padres.destroy', $padre) }}" method="POST" style="display:inline" onsubmit="return confirm('¿Está seguro de eliminar este padre y su cuenta de usuario? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            {{ $padres->appends(['search' => $search])->links() }}
        </div>
    </div>

    <!-- Modal Registro -->
    <div class="modal fade" id="modalRegistro" tabindex="-1" role="dialog" aria-labelledby="modalRegistroLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('padres.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="modalRegistroLabel">Registrar Nuevo Padre</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Nombre(s)</label>
                                    <input type="text" name="nombre" class="form-control" required value="{{ old('nombre') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Apellido Paterno</label>
                                    <input type="text" name="apellido_paterno" class="form-control" required value="{{ old('apellido_paterno') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Apellido Materno</label>
                                    <input type="text" name="apellido_materno" class="form-control" value="{{ old('apellido_materno') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email (Cuenta de usuario)</label>
                                    <input type="email" name="email" class="form-control" required placeholder="ejemplo@correo.com" value="{{ old('email') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password Provisional</label>
                                    <input type="password" name="password" class="form-control" required placeholder="******">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>CURP</label>
                                    <input type="text" name="curp" class="form-control" maxlength="18" required placeholder="18 caracteres" value="{{ old('curp') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Género</label>
                                    <select name="genero" class="form-control" required>
                                        <option value="">-- Seleccione --</option>
                                        <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Fecha de Nacimiento</label>
                                    <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono Fijo</label>
                                    <input type="text" name="telefono" class="form-control" required value="{{ old('telefono') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Celular</label>
                                    <input type="text" name="celular" class="form-control" value="{{ old('celular') }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Domicilio</label>
                            <textarea name="domicilio" class="form-control" rows="2">{{ old('domicilio') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Fotografía (Opcional)</label>
                            <div class="custom-file">
                                <input type="file" name="fotografia" class="custom-file-input" id="fotografia" accept="image/*">
                                <label class="custom-file-label" for="fotografia">Elegir archivo</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Registro</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Facturación -->
    <div class="modal fade" id="modalFacturacion" tabindex="-1" role="dialog" aria-labelledby="modalFacturacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form id="formFacturacion" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="modalFacturacionLabel">Actualizar Datos de Facturación</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>RFC</label>
                                    <input type="text" name="rfc" id="f_rfc" class="form-control" maxlength="13">
                                </div>
                                <div class="form-group">
                                    <label>Razón Social</label>
                                    <input type="text" name="razon_social" id="f_razon_social" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Calle</label>
                                    <input type="text" name="calle" id="f_calle" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Número</label>
                                    <input type="text" name="numero" id="f_numero" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Colonia</label>
                                    <input type="text" name="colonia" id="f_colonia" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ciudad</label>
                                    <input type="text" name="ciudad" id="f_ciudad" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Código Postal</label>
                                    <input type="text" name="codigo_postal" id="f_codigo_postal" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>SEP</label>
                                    <input type="text" name="sep" id="f_sep" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>SAT</label>
                                    <select name="sat" id="f_sat" class="form-control">
                                        <option value="">-- Seleccione --</option>
                                        @foreach($satConceptos as $sat)
                                            <option value="{{ $sat->clave }} - {{ $sat->descripcion }}">{{ $sat->clave }} - {{ $sat->descripcion }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select name="estado" id="f_estado" class="form-control">
                                        <option value="">-- Seleccione --</option>
                                        @php
                                            $estados = ['Aguascalientes', 'Baja California', 'Baja California Sur', 'Campeche', 'Chiapas', 'Chihuahua', 'Ciudad de México', 'Coahuila', 'Colima', 'Durango', 'Estado de México', 'Guanajuato', 'Guerrero', 'Hidalgo', 'Jalisco', 'Michoacán', 'Morelos', 'Nayarit', 'Nuevo León', 'Oaxaca', 'Puebla', 'Querétaro', 'Quintana Roo', 'San Luis Potosí', 'Sinaloa', 'Sonora', 'Tabasco', 'Tamaulipas', 'Tlaxcala', 'Veracruz', 'Yucatán', 'Zacatecas'];
                                        @endphp
                                        @foreach($estados as $estado)
                                            <option value="{{ $estado }}">{{ $estado }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success">Actualizar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hijos -->
    <div class="modal fade" id="modalHijos" tabindex="-1" role="dialog" aria-labelledby="modalHijosLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white" id="modalHijosLabel">Hijos Relacionados</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="list-group" id="listaHijos">
                        <!-- Se llena vía JS -->
                    </ul>
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
        // Lógica Facturación
        $('.btn-facturacion').click(function() {
            let id = $(this).data('id');
            let url = $(this).data('url');
            let data = $(this).data('json');

            $('#formFacturacion').attr('action', url);
            
            // Limpiar campos
            $('#formFacturacion input[type="text"]').val('');

            if (data) {
                $('#f_rfc').val(data.rfc);
                $('#f_razon_social').val(data.razon_social);
                $('#f_calle').val(data.calle);
                $('#f_numero').val(data.numero);
                $('#f_colonia').val(data.colonia);
                $('#f_ciudad').val(data.ciudad);
                $('#f_codigo_postal').val(data.codigo_postal);
                $('#f_sep').val(data.sep);
                $('#f_sat').val(data.sat);
                $('#f_estado').val(data.estado);
            }

            $('#modalFacturacion').modal('show');
        });

        // Lógica Hijos
        $('.btn-hijos').click(function() {
            let id = $(this).data('id');
            $('#listaHijos').html('<li class="list-group-item">Cargando...</li>');
            $('#modalHijos').modal('show');

            $.get('{{ url("padres") }}/' + id + '/children', function(hijos) {
                $('#listaHijos').empty();
                if (hijos.length === 0) {
                    $('#listaHijos').append('<li class="list-group-item">No hay hijos registrados.</li>');
                } else {
                    hijos.forEach(function(hijo) {
                        let grado = hijo.grado_grupo ? (hijo.grado_grupo.grado + ' ' + hijo.grado_grupo.grupo) : 'Sin grado';
                        $('#listaHijos').append(
                            '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                            '<div><strong>' + hijo.nombre + ' ' + hijo.apellidos + '</strong><br>' +
                            '<small class="text-muted">' + hijo.matricula + ' - ' + grado + '</small></div>' +
                            '<span class="badge badge-primary badge-pill">Alumno</span>' +
                            '</li>'
                        );
                    });
                }
            });
        });
    });
</script>
@stop
