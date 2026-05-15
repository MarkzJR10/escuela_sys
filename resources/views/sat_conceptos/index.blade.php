@extends('adminlte::page')

@section('title', 'Conceptos SAT')

@section('content_header')
    <h1>Conceptos SAT</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('sat_conceptos.create') }}" class="btn btn-primary">Nuevo Concepto</a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($conceptos as $concepto)
                    <tr>
                        <td>{{ $concepto->clave }}</td>
                        <td>{{ $concepto->descripcion }}</td>
                        <td>
                            <span class="badge badge-{{ $concepto->active ? 'success' : 'danger' }}">
                                {{ $concepto->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('sat_conceptos.edit', $concepto) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('sat_conceptos.destroy', $concepto) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar concepto?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop
