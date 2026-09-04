@extends('adminlte::page')

@section('title', 'Recibos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1">Recibos</h1>
            <h4 class="text-secondary font-weight-normal">Recibo de Pago de <strong>{{ strtoupper($alumno->nombre_completo) }}</strong></h4>
        </div>
        <div>
            <a href="{{ route('portal_padre.dashboard') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Mis Hijos
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print mr-1"></i> Imprimir Recibo
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="row justify-content-start">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="receipt-ticket bg-white p-4 shadow-sm">
            
            <!-- Top Box: Banco / Cuenta -->
            <div class="receipt-header-row d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="bank-logo-container pr-3">
                    <img src="{{ asset('img/inbursa.png') }}" alt="INBURSA Grupo Financiero" style="max-height: 55px; width: auto;">
                </div>
                
                <div class="account-info-box border p-2 text-left" style="min-width: 220px; font-size: 13px; line-height: 1.4;">
                    <div><strong>No. Cuenta:</strong> 50010756760</div>
                    <div><strong>Clabe:</strong> 036580500107567605</div>
                </div>
            </div>

            <!-- Business Name Box -->
            <div class="text-center py-2 mb-3 border">
                <h5 class="m-0 font-weight-bold text-dark text-uppercase" style="letter-spacing: 1px;">
                    IBEROAMERICANO Y ASOCIADOS SC
                </h5>
            </div>

            <!-- Student & Reference Box -->
            <div class="student-info-box border p-3 mb-4 text-center">
                <div class="mb-2">
                    <span class="font-weight-bold">Alumno:</span> {{ strtoupper($alumno->nombre_completo) }}
                </div>
                <div class="mb-2">
                    <span class="font-weight-bold">Referencia:</span> <span id="ref-text" class="h5 font-weight-bold text-dark">{{ $referencia }}</span>
                </div>
                <div class="barcode-container my-3 d-flex flex-column align-items-center">
                    <svg id="barcode"></svg>
                </div>
            </div>

            <!-- Rates / Dates -->
            <div class="rates-box text-center mb-4 font-weight-bold text-dark" style="font-size: 15px;">
                <div class="mb-2">Pagando del día 1 al 10 ${{ number_format($montoPronto, 0) }}</div>
                <div>Pagando del día 11 al 30 ${{ number_format($montoRegular, 0) }}</div>
            </div>

            <!-- Notes / Footer -->
            <div class="notes-box border-top pt-3 text-uppercase font-weight-bold text-muted" style="font-size: 11px; line-height: 1.5;">
                <p class="mb-2">*PARA TRANSFERENCIAS Y/O PAGO EN VENTANILLA FAVOR DE ANOTAR LA REFERENCIA SIN GUIONES NI ESPACIOS YA QUE NO SERA POSIBLE LOCALIZAR SU PAGO SIN ELLA</p>
                <p class="mb-0">*SI USTED YA REALIZO SU PAGO FAVOR DE HACER CASO OMISO</p>
            </div>

        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .receipt-ticket {
        border: 2px solid #222;
        max-width: 540px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    
    .border {
        border: 1.5px solid #222 !important;
    }

    .border-bottom {
        border-bottom: 1.5px solid #222 !important;
    }

    .border-top {
        border-top: 1.5px solid #222 !important;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .receipt-ticket, .receipt-ticket * {
            visibility: visible;
        }
        .receipt-ticket {
            position: absolute;
            left: 50%;
            top: 20px;
            transform: translateX(-50%);
            width: 100%;
            max-width: 500px;
            border: 2px solid #000 !important;
            box-shadow: none !important;
        }
        .main-header, .main-sidebar, .content-header, .btn {
            display: none !important;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    $(document).ready(function() {
        var ref = "{{ $referencia }}";
        if (ref) {
            JsBarcode("#barcode", ref, {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 50,
                displayValue: true,
                fontSize: 14,
                margin: 5
            });
        }
    });
</script>
@stop
