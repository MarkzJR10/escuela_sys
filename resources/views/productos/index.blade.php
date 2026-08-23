@extends('adminlte::page')

@section('title', 'Catálogo de Productos')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Catálogo de Productos</h1>
        </div>
        <div class="col-sm-6 text-right">
            <button type="button" class="btn btn-secondary mr-2" data-toggle="modal" data-target="#modalBitacoraStock">
                <i class="fas fa-history mr-1"></i> Bitácora de Stock
            </button>
            <a href="{{ route('productos.exportar_excel') }}" class="btn btn-success mr-2">
                <i class="fas fa-file-excel mr-1"></i> Descargar Excel
            </a>
            <a href="{{ route('productos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Producto
            </a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body table-responsive">
            <table id="productos-table" class="table table-hover table-striped">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock Actual</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 220px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                        <tr>
                            <td>{{ $producto->id }}</td>
                            <td><strong>{{ $producto->nombre }}</strong></td>
                            <td>${{ number_format($producto->precio, 2) }}</td>
                            <td>
                                @if($producto->stock > 0)
                                    <span class="badge badge-success px-2 py-1" style="font-size: 13px;">{{ $producto->stock }} unidades</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1" style="font-size: 13px;">Agotado</span>
                                @endif
                            </td>
                            <td>
                                @if($producto->activo)
                                    <span class="badge badge-primary">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <!-- Botón Agregar Stock -->
                                <button type="button" 
                                        class="btn btn-sm btn-success btn-agregar-stock mr-1" 
                                        data-id="{{ $producto->id }}" 
                                        data-nombre="{{ $producto->nombre }}" 
                                        data-stock="{{ $producto->stock }}" 
                                        title="Agregar Stock">
                                    <i class="fas fa-plus-circle mr-1"></i> Stock
                                </button>

                                <!-- Editar -->
                                <a href="{{ route('productos.edit', $producto) }}" class="btn btn-sm btn-info" title="Editar Producto">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <!-- Eliminar -->
                                <form action="{{ route('productos.destroy', $producto) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Eliminar producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Producto">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Agregar Stock -->
    <div class="modal fade" id="modalAgregarStock" tabindex="-1" role="dialog" aria-labelledby="modalAgregarStockLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formAgregarStock" method="POST" action="">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="modalAgregarStockLabel">
                            <i class="fas fa-boxes mr-1"></i> Agregar Stock a Producto
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-light border mb-3">
                            <strong id="modalProductoNombre" class="text-primary h5 d-block"></strong>
                            <span class="text-muted">Stock actual en sistema: </span>
                            <span id="modalProductoStockActual" class="font-weight-bold text-dark h6"></span> unidades
                        </div>

                        <div class="form-group">
                            <label for="cantidad_agregar"><i class="fas fa-plus text-success mr-1"></i> Cantidad de Stock a Agregar:</label>
                            <input type="number" name="cantidad" id="cantidad_agregar" class="form-control form-control-lg" min="1" value="1" required placeholder="Ej. 10">
                        </div>

                        <div class="form-group">
                            <label for="motivo_agregar"><i class="fas fa-comment-alt text-secondary mr-1"></i> Motivo / Observaciones (Opcional):</label>
                            <input type="text" name="motivo" id="motivo_agregar" class="form-control" placeholder="Ej. Compra a proveedor, Reabastecimiento...">
                        </div>

                        <div class="p-3 bg-light rounded text-center border">
                            <span class="text-muted">Stock Total Estimado Resultante: </span>
                            <span id="modalStockResultante" class="h4 font-weight-bold text-success ml-1">0</span> unidades
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success font-weight-bold">
                            <i class="fas fa-check-circle mr-1"></i> Guardar Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Bitácora de Stock -->
    <div class="modal fade" id="modalBitacoraStock" tabindex="-1" role="dialog" aria-labelledby="modalBitacoraStockLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="modalBitacoraStockLabel">
                        <i class="fas fa-history mr-1"></i> Bitácora de Reabastecimiento de Stock
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if(isset($bitacoras) && $bitacoras->count() > 0)
                        <div class="table-responsive">
                            <table id="bitacora-table" class="table table-bordered table-striped text-sm w-100">
                                <thead class="bg-secondary text-white">
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad Agregada</th>
                                        <th class="text-center">Stock (Anterior &rarr; Nuevo)</th>
                                        <th>Usuario que Agregó</th>
                                        <th>Motivo / Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bitacoras as $bitacora)
                                        <tr>
                                            <td>{{ $bitacora->created_at ? $bitacora->created_at->format('d/m/Y h:i A') : 'N/A' }}</td>
                                            <td><strong>{{ optional($bitacora->producto)->nombre ?? 'Producto Eliminado' }}</strong></td>
                                            <td class="text-center font-weight-bold text-success">
                                                <span class="badge badge-success px-2 py-1" style="font-size: 13px;">
                                                    +{{ $bitacora->cantidad_agregada }} unidades
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-muted">{{ $bitacora->stock_anterior }}</span>
                                                <i class="fas fa-arrow-right mx-1 text-secondary"></i>
                                                <strong class="text-dark">{{ $bitacora->stock_nuevo }}</strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-user text-info mr-1"></i>{{ optional($bitacora->usuario)->name ?? 'Sistema' }}
                                            </td>
                                            <td>{{ $bitacora->motivo ?: 'Sin observación' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle mr-1"></i> Aún no se han registrado movimientos de entrada de stock en la bitácora.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('plugins.Datatables', true)

@section('js')
<script>
    $(document).ready(function() {
        $('#productos-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "responsive": true,
            "columnDefs": [
                { "orderable": false, "targets": 5 }
            ]
        });

        $('#bitacora-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json"
            },
            "pageLength": 10,
            "order": [[0, "desc"]],
            "responsive": true
        });

        // Evento Abrir Modal Agregar Stock
        let stockActualBase = 0;

        $('.btn-agregar-stock').on('click', function() {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');
            stockActualBase = parseInt($(this).data('stock')) || 0;

            $('#modalProductoNombre').text(nombre);
            $('#modalProductoStockActual').text(stockActualBase);
            $('#cantidad_agregar').val(1);
            $('#motivo_agregar').val('');

            // Set Form Action URL
            let actionUrl = "{{ url('productos') }}/" + id + "/agregar-stock";
            $('#formAgregarStock').attr('action', actionUrl);

            calcularStockResultante();
            $('#modalAgregarStock').modal('show');
        });

        $('#cantidad_agregar').on('input change', function() {
            calcularStockResultante();
        });

        function calcularStockResultante() {
            let cant = parseInt($('#cantidad_agregar').val()) || 0;
            if (cant < 1) cant = 0;
            let total = stockActualBase + cant;
            $('#modalStockResultante').text(total);
        }
    });
</script>
@stop
