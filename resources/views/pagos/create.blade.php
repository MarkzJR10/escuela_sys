@extends('adminlte::page')

@section('title', 'Procesar Cobro - ' . $alumno->nombre)

@section('content_header')
    <h1>Procesar Cobro</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Selección de Adeudos: {{ $alumno->nombre }} {{ $alumno->apellidos }} ({{ $alumno->matricula }})</h3>
                </div>
                <form action="{{ route('pagos.store', $alumno->id) }}" method="POST" id="payment-form">
                    @csrf
                    <div class="card-body p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">Pagar</th>
                                    <th>Concepto / Mes</th>
                                    <th class="text-right">Monto Original</th>
                                    <th class="text-right" style="width: 150px;">Descuento ($)</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($adeudos as $adeudo)
                                    <tr class="adeudo-row" data-id="{{ $adeudo->id }}" data-original="{{ $adeudo->monto_actual }}">
                                        <td>
                                            <div class="custom-control custom-checkbox">
                                                <input class="custom-control-input adeudo-checkbox" type="checkbox" id="check_{{ $adeudo->id }}" name="adeudo_ids[]" value="{{ $adeudo->id }}">
                                                <label for="check_{{ $adeudo->id }}" class="custom-control-label"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>{{ $adeudo->tipo == 'colegiatura' ? 'Colegiatura ' . $adeudo->mes_nombre : $adeudo->concepto }}</strong>
                                            <br>
                                            <small class="text-muted">Base: ${{ number_format($adeudo->monto_base, 2) }}</small>
                                        </td>
                                        <td class="text-right">
                                            ${{ number_format($adeudo->monto_actual, 2) }}
                                        </td>
                                        <td class="text-right">
                                            <input type="number" step="0.01" min="0" max="{{ $adeudo->monto_actual }}" name="descuentos[{{ $adeudo->id }}]" class="form-control form-control-sm text-right discount-input" value="0.00" disabled>
                                        </td>
                                        <td class="text-right font-weight-bold subtotal-cell">
                                            $0.00
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">El alumno no tiene adeudos pendientes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary float-right" id="btn-pay" disabled>
                            <i class="fas fa-hand-holding-usd"></i> Generar Pago y Ticket
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dark">
                <div class="card-header">
                    <h3 class="card-title">Resumen de Cobro</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Adeudos Seleccionados:</span>
                        <span id="selected-count">0</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                         <span>Monto Debido:</span>
                         <span id="total-due">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Total Descuentos:</span>
                        <span id="total-discounts">$0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between font-weight-bold" style="font-size: 1.5rem;">
                        <span>Total a Pagar:</span>
                        <span id="grand-total">$0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(function() {
        function calculate() {
            let totalDue = 0;
            let totalDiscounts = 0;
            let grandTotal = 0;
            let count = 0;

            $('.adeudo-row').each(function() {
                const row = $(this);
                const checkbox = row.find('.adeudo-checkbox');
                const discountInput = row.find('.discount-input');
                const subtotalCell = row.find('.subtotal-cell');
                const originalMonto = parseFloat(row.data('original'));

                if (checkbox.is(':checked')) {
                    count++;
                    discountInput.prop('disabled', false);
                    let discount = parseFloat(discountInput.val()) || 0;
                    
                    if (discount > originalMonto) {
                        discount = originalMonto;
                        discountInput.val(discount.toFixed(2));
                    }

                    let subtotal = originalMonto - discount;
                    
                    totalDue += originalMonto;
                    totalDiscounts += discount;
                    grandTotal += subtotal;
                    
                    subtotalCell.text('$' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
                } else {
                    discountInput.prop('disabled', true);
                    subtotalCell.text('$0.00');
                }
            });

            $('#selected-count').text(count);
            $('#total-due').text('$' + totalDue.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#total-discounts').text('$' + totalDiscounts.toLocaleString('en-US', {minimumFractionDigits: 2}));
            $('#grand-total').text('$' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
            
            $('#btn-pay').prop('disabled', count === 0);
        }

        $('.adeudo-checkbox, .discount-input').on('change input', function() {
            calculate();
        });

        // Verificación final antes de enviar
        $('#payment-form').on('submit', function() {
            return confirm('¿Está seguro de procesar este pago? Se marcarán los adeudos como pagados y se generará el ticket.');
        });
    });
</script>
@stop
