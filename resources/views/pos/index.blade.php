@extends('adminlte::page')

@section('title', 'Punto de Venta')

@section('content_header')
    <h1>Punto de Venta</h1>
@stop

@section('content')
<div class="row" id="pos-app">
    <!-- Panel Izquierdo: Selección -->
    <div class="col-md-8">
        <!-- Buscador de Alumnos -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search"></i> Buscar Alumno / Padre</h3>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <input type="text" id="alumno-search" class="form-control form-control-lg" placeholder="Nombre o Matrícula...">
                    <div class="input-group-append">
                        <button class="btn btn-primary btn-lg"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div id="search-results" class="list-group mt-2" style="display:none; position:absolute; z-index:1000; width:95%;"></div>
                
                <div id="selected-alumno" class="mt-3 p-3 bg-light border rounded" style="display:none;">
                    <div class="row">
                        <div class="col-sm-8">
                            <h4 id="alumno-nombre" class="mb-0"></h4>
                            <p id="alumno-info" class="text-muted mb-0"></p>
                        </div>
                        <div class="col-sm-4 text-right">
                            <button class="btn btn-sm btn-danger" onclick="resetPOS()"><i class="fas fa-times"></i> Cambiar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestañas de Selección -->
        <div class="card card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="pos-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tabs-productos-tab" data-toggle="pill" href="#tabs-productos" role="tab">Catálogo de Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-adeudos-tab" data-toggle="pill" href="#tabs-adeudos" role="tab">Adeudos Pendientes</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="pos-tabs-content">
                    <!-- Catálogo de Productos -->
                    <div class="tab-pane fade show active" id="tabs-productos" role="tabpanel">
                        <div class="row">
                            @foreach($productos as $producto)
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="info-box bg-white shadow-sm h-100 product-card" onclick="addToCart({{ json_encode($producto) }}, 'producto')">
                                        <span class="info-box-icon bg-info"><i class="fas fa-tag"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text"><strong>{{ $producto->nombre }}</strong></span>
                                            <span class="info-box-number text-success">${{ number_format($producto->precio, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Adeudos Pendientes -->
                    <div class="tab-pane fade" id="tabs-adeudos" role="tabpanel">
                        <div id="adeudos-list" class="list-group">
                            <p class="text-center text-muted p-4">Selecciona un alumno para ver sus adeudos.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Derecho: Carrito -->
    <div class="col-md-4">
        <div class="card card-outline card-success sticky-top" style="top: 20px;">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shopping-cart"></i> Detalle de Venta</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-valign-middle mb-0" id="cart-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">Precio</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <tr>
                            <td colspan="4" class="text-center text-muted p-4">Carrito vacío</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between mb-2">
                    <span class="h5">Total:</span>
                    <span class="h5 text-success font-weight-bold" id="cart-total">$0.00</span>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <button class="btn btn-outline-primary btn-block btn-lg" id="btn-adeudo" disabled onclick="processSale('cargar_adeudo')">
                            <i class="fas fa-hand-holding-usd"></i><br><small>Cargar a Adeudo</small>
                        </button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-success btn-block btn-lg" id="btn-cobrar" disabled onclick="processSale('pago_inmediato')">
                            <i class="fas fa-cash-register"></i><br><strong>Cobrar</strong>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Sweetalert2', true)
@section('plugins.Toastr', true)

@section('css')
<style>
    .product-card { cursor: pointer; transition: transform 0.2s; }
    .product-card:hover { transform: scale(1.02); box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important; }
    .sticky-top { z-index: 10; }
    #search-results { max-height: 300px; overflow-y: auto; }
</style>
@stop

@section('js')
<script>
    let selectedAlumno = null;
    let cart = [];

    $(document).ready(function() {
        $('#alumno-search').on('keyup', function() {
            let term = $(this).val();
            if (term.length < 3) {
                $('#search-results').hide();
                return;
            }

            $.get('{{ route("pos.buscar_alumno") }}', { term: term }, function(data) {
                let html = '';
                data.forEach(alumno => {
                    html += `<a href="#" class="list-group-item list-group-item-action" onclick="selectAlumno(${JSON.stringify(alumno).replace(/"/g, '&quot;')})">
                        <strong>${alumno.nombre} ${alumno.apellido_paterno}</strong><br>
                        <small>Matrícula: ${alumno.matricula} | ${alumno.grado_grupo.grado} ${alumno.grado_grupo.grupo}</small>
                    </a>`;
                });
                $('#search-results').html(html).show();
            });
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('#alumno-search').length) {
                $('#search-results').hide();
            }
        });
    });

    function selectAlumno(alumno) {
        selectedAlumno = alumno;
        $('#alumno-search').val('');
        $('#search-results').hide();
        $('#selected-alumno').show();
        $('#alumno-nombre').text(`${alumno.nombre} ${alumno.apellido_paterno} ${alumno.apellido_materno || ''}`);
        $('#alumno-info').text(`Matrícula: ${alumno.matricula} | ${alumno.grado_grupo.grado} ${alumno.grado_grupo.grupo}`);
        updateButtons();
        loadAdeudos(alumno.id);
    }

    function resetPOS() {
        selectedAlumno = null;
        cart = [];
        $('#selected-alumno').hide();
        $('#adeudos-list').html('<p class="text-center text-muted p-4">Selecciona un alumno para ver sus adeudos.</p>');
        renderCart();
        updateButtons();
    }

    function loadAdeudos(alumnoId) {
        $.get(`{{ url('pos/adeudos') }}/${alumnoId}`, function(data) {
            let html = '';
            if (data.length === 0) {
                html = '<p class="text-center text-muted p-4">No tiene adeudos pendientes.</p>';
            } else {
                data.forEach(adeudo => {
                    let statusClass = 'secondary';
                    if (adeudo.status === 'vencido') statusClass = 'danger';
                    if (adeudo.status === 'pendiente') statusClass = 'warning';
                    if (adeudo.status === 'programado') statusClass = 'info';

                    html += `<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="addToCart(${JSON.stringify(adeudo).replace(/"/g, '&quot;')}, 'adeudo_existente')">
                        <div>
                            <span class="badge badge-${statusClass} mr-2">${adeudo.status.toUpperCase()}</span>
                            <strong>${adeudo.concepto}</strong><br>
                            <small class="text-muted">${adeudo.periodo || ''}</small>
                        </div>
                        <span class="h6 mb-0">$${parseFloat(adeudo.monto_calculado).toFixed(2)}</span>
                    </div>`;
                });
            }
            $('#adeudos-list').html(html);
        });
    }

    function addToCart(item, tipo) {
        if (!selectedAlumno) {
            toastr.warning('Primero selecciona un alumno.');
            return;
        }

        let existing = cart.find(i => i.id === item.id && i.tipo === tipo);
        if (existing && tipo === 'producto') {
            existing.cantidad++;
        } else if (existing && tipo === 'adeudo_existente') {
            toastr.info('Este adeudo ya está en el carrito.');
            return;
        } else {
            cart.push({
                id: item.id,
                nombre: tipo === 'producto' ? item.nombre : item.concepto,
                precio: parseFloat(tipo === 'producto' ? item.precio : item.monto_calculado),
                tipo: tipo,
                cantidad: 1,
                descuento: 0
            });
        }
        renderCart();
    }

    function updateDiscount(index, value) {
        cart[index].descuento = parseFloat(value) || 0;
        renderCart(false); // No volver a renderizar los inputs para no perder el foco
        updateTotalOnly();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function renderCart(fullRender = true) {
        let body = $('#cart-body');
        if (cart.length === 0) {
            body.html('<tr><td colspan="4" class="text-center text-muted p-4">Carrito vacío</td></tr>');
            $('#cart-total').text('$0.00');
            updateButtons();
            return;
        }

        if (fullRender) {
            let html = '';
            cart.forEach((item, index) => {
                let subtotal = (item.precio * item.cantidad) - item.descuento;
                html += `<tr>
                    <td>
                        <small class="badge badge-${item.tipo === 'producto' ? 'info' : 'warning'}">${item.tipo === 'producto' ? 'P' : 'A'}</small> 
                        ${item.nombre}
                        <div class="mt-1">
                            <input type="number" class="form-control form-control-sm d-inline-block" style="width: 80px;" 
                                placeholder="Desc." value="${item.descuento}" onchange="updateDiscount(${index}, this.value)">
                            <small class="text-muted ml-1">Desc.</small>
                        </div>
                    </td>
                    <td class="text-center">${item.cantidad}</td>
                    <td class="text-right">$${subtotal.toFixed(2)}</td>
                    <td class="text-right">
                        <button class="btn btn-xs btn-link text-danger" onclick="removeFromCart(${index})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
            body.html(html);
        }
        updateTotalOnly();
        updateButtons();
    }

    function updateTotalOnly() {
        let total = 0;
        cart.forEach(item => {
            total += (item.precio * item.cantidad) - item.descuento;
        });
        $('#cart-total').text(`$${total.toFixed(2)}`);
    }

    function updateButtons() {
        let hasCart = cart.length > 0;
        let hasOnlyProducts = cart.every(i => i.tipo === 'producto');
        
        $('#btn-cobrar').prop('disabled', !hasCart);
        // Solo se puede cargar a adeudo si hay productos nuevos. 
        // Si hay adeudos existentes en el carrito, cargar a adeudo no tiene sentido (ya lo son).
        // Sin embargo, el requerimiento dice "pagar adeudos... o agregar producto... se queda como pendiente"
        $('#btn-adeudo').prop('disabled', !hasCart || !hasOnlyProducts);
    }

    function processSale(metodo) {
        if (!selectedAlumno || cart.length === 0) return;

        Swal.fire({
            title: metodo === 'pago_inmediato' ? '¿Procesar Cobro?' : '¿Cargar a Adeudo?',
            text: metodo === 'pago_inmediato' ? 'Se generará el recibo de pago.' : 'Los productos se agregarán a la cuenta del alumno.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.post('{{ route("pos.procesar") }}', {
                    _token: '{{ csrf_token() }}',
                    alumno_id: selectedAlumno.id,
                    items: cart,
                    metodo: metodo
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Error: ${error.responseJSON.message || 'No se pudo procesar la venta'}`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.value && result.value.success) {
                const response = result.value;
                
                // Si hay pago, intentamos abrir el ticket inmediatamente antes del alert final
                // para que el navegador lo asocie mejor con el click del usuario
                if (response.pago_id) {
                    const ticketUrl = `{{ url('pagos/ticket') }}/${response.pago_id}`;
                    window.open(ticketUrl, '_blank');
                }

                Swal.fire({
                    title: '¡Éxito!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Recargar para limpiar todo y refrescar saldos
                    location.reload();
                });
            }
        });
    }
</script>
@stop
