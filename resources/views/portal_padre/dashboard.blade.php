@extends('adminlte::page')

@section('title', 'Mis Hijos')

@section('content_header')
    <h1>Portal Padre: Mis Hijos</h1>
@stop

@section('content')
<div class="row">
    @foreach($hijos as $hijo)
    <div class="col-md-4">
        <div class="card card-widget widget-user-2 shadow-sm">
            <div class="widget-user-header bg-primary">
                <div class="widget-user-image">
                    @if($hijo->fotografia)
                        <img class="img-circle elevation-2" src="{{ asset('storage/'.$hijo->fotografia) }}" alt="User Avatar">
                    @else
                        <img class="img-circle elevation-2" src="{{ asset('vendor/adminlte/dist/img/avatar.png') }}" alt="User Avatar">
                    @endif
                </div>
                <h3 class="widget-user-username">{{ $hijo->nombre }}</h3>
                <h5 class="widget-user-desc">{{ $hijo->gradoGrupo->grado ?? '' }} "{{ $hijo->gradoGrupo->grupo ?? '' }}"</h5>
            </div>
            <div class="card-footer p-0">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="{{ route('portal_padre.boleta', $hijo->id) }}" class="nav-link">
                            <i class="fas fa-file-pdf text-danger"></i> Boleta de Calificaciones
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal_padre.conducta', $hijo->id) }}" class="nav-link">
                            <i class="fas fa-exclamation-triangle text-warning"></i> Reportes de Conducta
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal_padre.estado_cuenta', $hijo->id) }}" class="nav-link">
                            <i class="fas fa-file-invoice-dollar text-success"></i> Estado de Cuenta
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('portal_padre.recibo', $hijo->id) }}" class="nav-link">
                            <i class="fas fa-receipt text-info"></i> Recibos
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    @endforeach
    
    @if($hijos->isEmpty())
        <div class="col-12">
            <div class="alert alert-warning">No tiene hijos vinculados a su cuenta. Contacte a control escolar.</div>
        </div>
    @endif
</div>
@stop
