@extends('adminlte::page')

@section('title', 'Dashboard - Escuela Sys')

@section('content_header')
    <h1>Panel de Administración</h1>
@stop

@section('content')
    {{-- Contadores Base --}}
    <div class="row">
        <!-- Alumnos -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $alumnosCount }}</h3>
                    <p>Alumnos Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <a href="{{ route('alumnos.index') }}" class="small-box-footer" style="color:white !important;">Ver detalles <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        
        <!-- Grupos -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $gruposCount }}</h3>
                    <p>Grados y Grupos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <a href="{{ route('grado_grupos.index') }}" class="small-box-footer" style="color:white !important;">Ver detalles <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Materias -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $materiasCount }}</h3>
                    <p>Materias Totales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
                <a href="{{ route('materias.index') }}" class="small-box-footer" style="color:black !important;">Ver detalles <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <!-- Usuarios -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $usersCount }}</h3>
                    <p>Usuarios del Sistema</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('users.index') }}" class="small-box-footer" style="color:white !important;">Ver detalles <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Sección Financiera (admin, socio, cajero) --}}
    @if($isFinance)
    <div class="row">
        <div class="col-12">
            <h5 class="text-muted"><i class="fas fa-chart-line"></i> Resumen Financiero de Hoy</h5>
            <hr class="mt-0">
        </div>
        <div class="col-lg-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ingresos de Hoy</span>
                    <span class="info-box-number">${{ number_format($ingresosHoy, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-receipt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Tickets de Hoy</span>
                    <span class="info-box-number">{{ $ticketsHoy }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Adeudos Vencidos</span>
                    <span class="info-box-number">{{ $adeudosVencidos }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Accesos rápidos financieros --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bolt"></i> Accesos Rápidos</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('pos.index') }}" class="btn btn-success mr-2 mb-2">
                        <i class="fas fa-cash-register"></i> Punto de Venta
                    </a>
                    <a href="{{ route('colegiaturas.index') }}" class="btn btn-primary mr-2 mb-2">
                        <i class="fas fa-money-bill-wave"></i> Colegiaturas
                    </a>
                    <a href="{{ route('contabilidad.ventas') }}" class="btn btn-dark mr-2 mb-2">
                        <i class="fas fa-list"></i> Ventas del Día
                    </a>
                    <a href="{{ route('reportes.cobranza') }}" class="btn btn-info mr-2 mb-2">
                        <i class="fas fa-chart-bar"></i> Reporte Cobranza
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Sección Académica (maestro, coordinador, admin) --}}
    @if($isAcademic)
    <div class="row">
        <div class="col-12">
            <h5 class="text-muted"><i class="fas fa-graduation-cap"></i> Resumen Académico</h5>
            <hr class="mt-0">
        </div>
        <div class="col-lg-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Conducta Pendiente</span>
                    <span class="info-box-number">{{ $conductaPendiente }} <small>reportes sin leer</small></span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Profesores</span>
                    <span class="info-box-number">{{ $profesoresCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Accesos rápidos académicos --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-bolt"></i> Accesos Rápidos Académicos</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('calificaciones.index') }}" class="btn btn-primary mr-2 mb-2">
                        <i class="fas fa-star"></i> Calificaciones
                    </a>
                    <a href="{{ route('asistencias.index') }}" class="btn btn-success mr-2 mb-2">
                        <i class="fas fa-clipboard-check"></i> Asistencias
                    </a>
                    <a href="{{ route('reportes_conducta.pendientes') }}" class="btn btn-warning mr-2 mb-2">
                        <i class="fas fa-envelope"></i> Conducta Pendiente
                    </a>
                    <a href="{{ route('boletas.index') }}" class="btn btn-info mr-2 mb-2">
                        <i class="fas fa-file-pdf"></i> Boletas
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@stop
