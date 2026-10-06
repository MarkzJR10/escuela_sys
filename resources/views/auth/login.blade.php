@extends('adminlte::auth.login')

@section('css')
<style>
    body.login-page {
        background: linear-gradient(135deg, #eef2f7 0%, #dbe2ef 100%);
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    .login-box {
        width: 420px;
    }
    .login-logo a {
        font-weight: 600;
        letter-spacing: -0.5px;
    }
    .card {
        border-radius: 16px !important;
        border: none !important;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08) !important;
        overflow: hidden;
    }
    .card-header {
        border-bottom: 1px solid #f0f0f0 !important;
        background-color: #ffffff !important;
        padding: 1.5rem 1rem 1rem !important;
    }
    .card-header h3 {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
        color: #2c3e50 !important;
    }
    .card-body {
        padding: 1.75rem !important;
    }
    .card-footer {
        background-color: #fafbfc !important;
        border-top: 1px solid #f0f0f0 !important;
        padding: 1.25rem !important;
    }
    .form-control {
        border-radius: 8px 0 0 8px !important;
        height: 46px;
        font-size: 0.95rem;
        border-color: #e2e8f0;
    }
    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .input-group-text {
        border-radius: 0 8px 8px 0 !important;
        background-color: #f8fafc;
        border-color: #e2e8f0;
        color: #64748b;
    }
    .btn-primary {
        background-color: #0067b8 !important;
        border-color: #0067b8 !important;
        border-radius: 8px !important;
        min-height: 42px;
        height: auto;
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .btn-primary:hover {
        background-color: #005293 !important;
        border-color: #005293 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 103, 184, 0.3) !important;
    }
    .divider-text {
        position: relative;
        text-align: center;
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .divider-text span {
        padding: 0 12px;
        background-color: #fafbfc;
        position: relative;
        z-index: 2;
        color: #94a3b8;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .divider-text::after {
        content: "";
        position: absolute;
        bottom: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background-color: #e2e8f0;
        z-index: 1;
    }
    .google-signin-container {
        transition: transform 0.2s ease;
        display: inline-block;
    }
    .google-signin-container:hover {
        transform: translateY(-1px);
    }
    .institucional-tooltip-badge {
        display: inline-flex;
        align-items: center;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 20px;
        padding: 6px 14px;
        font-size: 0.825rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .institucional-tooltip-badge:hover {
        background: #dbeafe;
        color: #1e40af;
    }
</style>
@stop

@section('auth_body')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 8px;">
            <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @parent
@stop

@section('auth_footer')
    <div class="social-auth-links text-center mb-1">
        <div class="divider-text mb-3">
            <span>O</span>
        </div>

        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <div id="g_id_onload"
             data-client_id="{{ config('services.google.client_id') }}"
             data-callback="handleGoogleCredentialResponse"
             data-auto_prompt="false">
        </div>

        {{-- Google button with tooltip --}}
        <div class="google-signin-container" data-toggle="tooltip" data-placement="top" title="Usa tu Cuenta Institucional de familia">
            <div class="g_id_signin d-flex justify-content-center"
                 data-type="standard"
                 data-size="large"
                 data-theme="outline"
                 data-text="sign_in_with"
                 data-shape="rectangular"
                 data-logo_alignment="left"
                 data-width="320">
            </div>
        </div>

        <div class="mt-3">
            <span class="institucional-tooltip-badge" data-toggle="tooltip" data-placement="bottom" title="Usa tu Cuenta Institucional de familia">
                <i class="fas fa-info-circle mr-1"></i> Usa tu Cuenta Institucional de familia
            </span>
        </div>

        <form id="google-post-form" action="{{ route('login.google.post') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="credential" id="google_credential">
        </form>
    </div>
    {{-- @parent removed to hide password reset & register links --}}
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });

    function handleGoogleCredentialResponse(response) {
        if (response && response.credential) {
            document.getElementById('google_credential').value = response.credential;
            document.getElementById('google-post-form').submit();
        }
    }
</script>
@stop