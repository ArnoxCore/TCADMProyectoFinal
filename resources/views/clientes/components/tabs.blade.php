<section class="section">

    <div class="tabs">

        {{-- DASHBOARD --}}
        <a href="{{ route('cliente.dashboard') }}"
           class="tab {{ request()->routeIs('cliente.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        {{-- CITAS --}}
        <a href="{{ route('cliente.citas.index') }}"
           class="tab {{ request()->routeIs('cliente.citas.*') ? 'active' : '' }}">
            Mis Citas
        </a>

        {{-- VEHÍCULOS --}}
        <a href="{{ route('cliente.vehiculos.index') }}"
           class="tab {{ request()->routeIs('cliente.vehiculos.*') ? 'active' : '' }}">
            Mis Vehículos
        </a>

        {{-- PERFIL --}}
        <a href="{{ route('cliente.perfil') }}"
           class="tab {{ request()->routeIs('cliente.perfil') ? 'active' : '' }}">
            Perfil
        </a>

    </div>

</section>
