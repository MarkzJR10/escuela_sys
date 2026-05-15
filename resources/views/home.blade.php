@extends('adminlte::page')

@section('title', 'Dashboard - Escuela Sys')

@section('content_header')
    <h1>Panel de Administración</h1>
@stop

@section('content')
    <div class="row">
        <!-- Alumnos -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $alumnosCount }}</h3>
                    <p>Alumnos Registrados</p>
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
@stop
