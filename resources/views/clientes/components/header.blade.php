<header class="header">
    <div class="wrap">
        <div class="brand">
            <img src="{{ asset('frontend/icons/favicon.svg') }}" alt="Logo" class="brand-logo-img">
            <div>
                <div class="brand-title">Ctrl + Alt + Del Motors | Panel Cliente</div>
                <div class="brand-sub">Bienvenido, {{ Auth::user()->name }}</div>
            </div>
        </div>

        <div class="header-actions">

            <a href="{{ route('cliente.dashboard') }}"
               class="header-link {{ request()->routeIs('cliente.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('cliente.citas.index') }}"
               class="header-link {{ request()->routeIs('cliente.citas.*') ? 'active' : '' }}">
                Mis Citas
            </a>

            <a href="{{ route('cliente.vehiculos.index') }}"
               class="header-link {{ request()->routeIs('cliente.vehiculos.*') ? 'active' : '' }}">
                Mis Vehículos
            </a>

            <a href="{{ route('cliente.perfil') }}"
               class="header-link {{ request()->routeIs('cliente.perfil') ? 'active' : '' }}">
                Perfil
            </a>

            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button class="button ghost small" type="submit">Cerrar sesión</button>
            </form>

        </div>

    </div>
</header>
