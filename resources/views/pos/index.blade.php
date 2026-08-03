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
                                    <div class="info-box bg-white shadow-sm h-100 product-card {{ $producto->stock <= 0 ? 'opacity-50' : '' }}" 
                                         onclick="{{ $producto->stock > 0 ? 'addToCart('.json_encode($producto).', \'producto\')' : 'toastr.error(\'Producto agotado.\')' }}">
                                        <span class="info-box-icon {{ $producto->stock > 0 ? 'bg-info' : 'bg-secondary' }}"><i class="fas fa-tag"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text"><strong>{{ $producto->nombre }}</strong></span>
                                            <span class="info-box-number text-success mb-1">${{ number_format($producto->precio, 2) }}</span>
                                            <div>
                                                @if($producto->stock > 0)
                                                    <small class="text-muted"><i class="fas fa-boxes"></i> {{ $producto->stock }} unidades disponibles</small>
                                                @else
                                                    <span class="badge badge-danger">No disponible</span>
                                                @endif
                                            </div>
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
                <div class="form-group mb-3 px-2">
                    <label class="font-weight-bold"><i class="fas fa-wallet mr-1"></i> Método de Pago:</label>
                    <div class="custom-control custom-radio mb-2">
                        <input class="custom-control-input radio-metodo-pago" type="radio" id="metodo-efectivo" name="pos_metodo_pago" value="efectivo" checked>
                        <label for="metodo-efectivo" class="custom-control-label">
                            <i class="fas fa-money-bill-wave text-success mr-1"></i> Efectivo
                        </label>
                    </div>
                    <div class="custom-control custom-radio mb-2">
                        <input class="custom-control-input radio-metodo-pago" type="radio" id="metodo-tarjeta" name="pos_metodo_pago" value="tarjeta">
                        <label for="metodo-tarjeta" class="custom-control-label">
                            <i class="fas fa-credit-card text-primary mr-1"></i> Tarjeta Crédito/Débito
                        </label>
                    </div>
                    <div class="custom-control custom-radio mb-2" id="metodo-credito-wrapper">
                        <input class="custom-control-input radio-metodo-pago" type="radio" id="metodo-credito" name="pos_metodo_pago" value="credito">
                        <label for="metodo-credito" class="custom-control-label">
                            <i class="fas fa-hand-holding-usd text-warning mr-1"></i> A Crédito (Pendiente)
                        </label>
                    </div>
                </div>
                <hr>
                <button class="btn btn-success btn-block btn-lg" id="btn-cobrar" disabled onclick="submitPOSSale()">
                    <i class="fas fa-cash-register"></i> <span id="btn-cobrar-text" class="ml-1"><strong>Cobrar (Efectivo)</strong></span>
                </button>
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
            if (existing.cantidad >= item.stock) {
                toastr.error('No hay suficiente stock disponible.');
                return;
            }
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
                descuento: 0,
                monto_pagar: tipo === 'adeudo_existente' ? parseFloat(item.monto_calculado) : null,
                stock: tipo === 'producto' ? item.stock : null
            });
        }
        renderCart();
    }

    function updateAbono(index, value) {
        let maxVal = parseFloat(cart[index].precio);
        let val = parseFloat(value) || 0;
        if (val > maxVal) {
            val = maxVal;
            toastr.warning(`El abono no puede superar el saldo pendiente de $${maxVal.toFixed(2)}`);
        }
        if (val < 0.01) {
            val = 0.01;
        }
        cart[index].monto_pagar = val;
        renderCart(false);
        updateTotalOnly();
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
                let subtotal = 0;
                let inputHtml = '';
                
                if (item.tipo === 'producto') {
                    subtotal = (item.precio * item.cantidad) - item.descuento;
                    inputHtml = `
                        <input type="number" class="form-control form-control-sm d-inline-block" style="width: 80px;" 
                            placeholder="Desc." value="${item.descuento}" onchange="updateDiscount(${index}, this.value)">
                        <small class="text-muted ml-1">Desc.</small>
                    `;
                } else {
                    subtotal = item.monto_pagar;
                    inputHtml = `
                        <input type="number" class="form-control form-control-sm d-inline-block" style="width: 90px;" 
                            placeholder="Abonar" value="${item.monto_pagar}" min="0.01" max="${item.precio}" step="0.01"
                            onchange="updateAbono(${index}, this.value)">
                        <small class="text-muted ml-1">Monto a Pagar (Abono)</small>
                    `;
                }

                let qtyControl = item.tipo === 'producto' ? `
                    <div class="d-flex align-items-center justify-content-center">
                        <button class="btn btn-xs btn-outline-secondary px-1 py-0" onclick="decrementCartQty(${index})"><i class="fas fa-minus fa-xs"></i></button>
                        <span class="mx-2 font-weight-bold" style="min-width: 15px;">${item.cantidad}</span>
                        <button class="btn btn-xs btn-outline-secondary px-1 py-0" onclick="incrementCartQty(${index})"><i class="fas fa-plus fa-xs"></i></button>
                    </div>
                ` : `${item.cantidad}`;

                html += `<tr>
                    <td>
                        <small class="badge badge-${item.tipo === 'producto' ? 'info' : 'warning'}">${item.tipo === 'producto' ? 'P' : 'A'}</small> 
                        ${item.nombre}
                        <div class="mt-1">
                            ${inputHtml}
                        </div>
                    </td>
                    <td class="text-center">${qtyControl}</td>
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
            if (item.tipo === 'producto') {
                total += (item.precio * item.cantidad) - item.descuento;
            } else {
                total += item.monto_pagar;
            }
        });
        $('#cart-total').text(`$${total.toFixed(2)}`);
    }

    function updateButtons() {
        let hasCart = cart.length > 0;
        let hasOnlyProducts = cart.every(i => i.tipo === 'producto');
        
        $('#btn-cobrar').prop('disabled', !hasCart);

        // Si el carrito tiene adeudos existentes, no se permite "A Crédito"
        if (!hasOnlyProducts) {
            $('#metodo-credito').prop('disabled', true);
            $('#metodo-credito-wrapper').addClass('text-muted');
            if ($('#metodo-credito').is(':checked')) {
                $('#metodo-efectivo').prop('checked', true).trigger('change');
            }
        } else {
            // Permitir "A Crédito" si sólo hay productos nuevos
            $('#metodo-credito').prop('disabled', false);
            $('#metodo-credito-wrapper').removeClass('text-muted');
        }
    }

    $(document).on('change', '.radio-metodo-pago', function() {
        let val = $(this).val();
        let btn = $('#btn-cobrar');
        let btnText = $('#btn-cobrar-text');
        
        if (val === 'efectivo') {
            btn.removeClass('btn-primary btn-warning').addClass('btn-success');
            btnText.html('<strong>Cobrar (Efectivo)</strong>');
        } else if (val === 'tarjeta') {
            btn.removeClass('btn-success btn-warning').addClass('btn-primary');
            btnText.html('<strong>Cobrar (Tarjeta)</strong>');
        } else if (val === 'credito') {
            btn.removeClass('btn-success btn-primary').addClass('btn-warning');
            btnText.html('<strong>Cargar a Crédito</strong>');
        }
    });

    function submitPOSSale() {
        if (!selectedAlumno || cart.length === 0) return;

        let metodoPago = $('input[name="pos_metodo_pago"]:checked').val();
        let title = '¿Procesar Cobro?';
        let text = 'Se generará el recibo de pago.';
        
        if (metodoPago === 'credito') {
            title = '¿Cargar a Crédito?';
            text = 'Los productos se agregarán como adeudo pendiente a la cuenta del alumno.';
        }

        Swal.fire({
            title: title,
            text: text,
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
                    metodo_pago: metodoPago
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
                
                // Si hay pago, intentamos abrir el ticket inmediatamente
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
                    location.reload();
                });
            }
        });
    }

    function incrementCartQty(index) {
        let item = cart[index];
        if (item.tipo === 'producto') {
            if (item.cantidad >= item.stock) {
                toastr.error('No hay suficiente stock disponible.');
                return;
            }
            item.cantidad++;
            renderCart();
        }
    }

    function decrementCartQty(index) {
        let item = cart[index];
        if (item.tipo === 'producto') {
            if (item.cantidad <= 1) {
                removeFromCart(index);
                return;
            }
            item.cantidad--;
            renderCart();
        }
    }
</script>
@stop
