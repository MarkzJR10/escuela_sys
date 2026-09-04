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
        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <div id="g_id_onload"
             data-client_id="{{ config('services.google.client_id') }}"
             data-callback="handleGoogleCredentialResponse"
             data-auto_prompt="false">
        </div>
        <div class="g_id_signin d-flex justify-content-center"
             data-type="standard"
             data-size="large"
             data-theme="outline"
             data-text="sign_in_with"
             data-shape="rectangular"
             data-logo_alignment="left"
             data-width="300">
        </div>

        <form id="google-post-form" action="{{ route('login.google.post') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="credential" id="google_credential">
        </form>
    </div>
    @parent
@stop

@section('js')
<script>
    function handleGoogleCredentialResponse(response) {
        if (response && response.credential) {
            document.getElementById('google_credential').value = response.credential;
            document.getElementById('google-post-form').submit();
        }
    }
</script>
@stop