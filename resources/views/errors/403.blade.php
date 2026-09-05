@extends('adminlte::page')

@section('title', 'Acceso Denegado')

@section('content_header')
    <h1>Acceso Restringido</h1>
@stop

@section('content')
<div class="error-page my-5">
    <h2 class="headline text-warning"> 403</h2>
    <div class="error-content">
        <h3><i class="fas fa-exclamation-triangle text-warning"></i> Sin permisos.</h3>
        <p>
            Lo sentimos, no cuentas con los permisos para este menú, contactar a soporte.
        </p>
        <div class="mt-4">
            @if(Auth::check() && Auth::user()->hasRole('padre'))
                <a href="{{ route('portal_padre.dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left mr-1"></i> Ir al Portal de Padres
                </a>
            @else
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Inicio
                </a>
            @endif
        </div>
    </div>
</div>
@stop
