@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand-md') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    <div class="{{ config('adminlte.classes_topnav_container', 'container') }}">

        {{-- Navbar brand logo --}}
        @if(config('adminlte.logo_img_xl'))
            @include('adminlte::partials.common.brand-logo-xl')
        @else
            @include('adminlte::partials.common.brand-logo-xs')
        @endif

        {{-- Navbar toggler button --}}
        <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- Navbar collapsible menu --}}
        <div class="collapse navbar-collapse order-3" id="navbarCollapse">
            {{-- Navbar left links --}}
            <ul class="nav navbar-nav">
                {{-- Configured left links --}}
                @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

                {{-- Custom left links --}}
                @yield('content_top_nav_left')
            </ul>
        </div>

        {{-- Navbar right links --}}
        <ul class="navbar-nav ml-auto order-1 order-md-3 navbar-no-expand">
            {{-- Custom right links --}}
            @yield('content_top_nav_right')

            {{-- Fecha, Hora y Zona Horaria --}}
            <li class="nav-item d-flex align-items-center mr-2 mr-md-3">
                <span class="badge badge-light border px-2 py-1 shadow-sm text-secondary" style="font-size: 0.85rem;" title="Zona Horaria: {{ config('app.timezone', 'America/Mexico_City') }}">
                    <i class="far fa-clock text-primary mr-1"></i>
                    <span id="server-datetime-display-topnav" class="font-weight-bold">
                        {{ now()->timezone(config('app.timezone', 'America/Mexico_City'))->format('d/m/Y h:i:s A') }}
                    </span>
                    <span class="ml-1 badge badge-primary text-white" style="font-size: 0.7rem;">{{ config('app.timezone', 'America/Mexico_City') }}</span>
                </span>
            </li>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    let displayEl = document.getElementById('server-datetime-display-topnav');
                    if (displayEl) {
                        let serverTime = new Date("{{ now()->timezone(config('app.timezone', 'America/Mexico_City'))->toIso8601String() }}");
                        setInterval(function() {
                            serverTime.setSeconds(serverTime.getSeconds() + 1);
                            let day = String(serverTime.getDate()).padStart(2, '0');
                            let month = String(serverTime.getMonth() + 1).padStart(2, '0');
                            let year = serverTime.getFullYear();
                            let hours = serverTime.getHours();
                            let minutes = String(serverTime.getMinutes()).padStart(2, '0');
                            let seconds = String(serverTime.getSeconds()).padStart(2, '0');
                            let ampm = hours >= 12 ? 'PM' : 'AM';
                            hours = hours % 12;
                            hours = hours ? hours : 12;
                            let hoursStr = String(hours).padStart(2, '0');
                            displayEl.textContent = `${day}/${month}/${year} ${hoursStr}:${minutes}:${seconds} ${ampm}`;
                        }, 1000);
                    }
                });
            </script>

            {{-- Configured right links --}}
            @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

            {{-- User menu link --}}
            @if(Auth::user())
                @if(config('adminlte.usermenu_enabled'))
                    @include('adminlte::partials.navbar.menu-item-dropdown-user-menu')
                @else
                    @include('adminlte::partials.navbar.menu-item-logout-link')
                @endif
            @endif

            {{-- Right sidebar toggler link --}}
            @if($layoutHelper->isRightSidebarEnabled())
                @include('adminlte::partials.navbar.menu-item-right-sidebar-toggler')
            @endif
        </ul>

    </div>

</nav>
