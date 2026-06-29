@extends('adminlte::auth.login')

@section('auth_body')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @parent
@stop

@section('auth_footer')
    <div class="social-auth-links text-center mb-3">
        <p>- O -</p>
        <a href="{{ route('login.google') }}" class="btn btn-block btn-danger">
            <i class="fab fa-google mr-2"></i> Iniciar sesión con Google
        </a>
    </div>
    @parent
@stop