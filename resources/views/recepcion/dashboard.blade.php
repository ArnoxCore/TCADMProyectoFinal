{{-- resources/views/recepcion/dashboard.blade.php --}}
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Recepción - Ctrl+Alt+Del Motors</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF para peticiones AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- CSS del panel de recepción (nuevo) --}}
    <link rel="stylesheet" href="{{ asset('frontend/Panel-Recepcionista/recepcion.css') }}">

    <!-- ====== FAVICON / PWA ====== -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('frontend/icons/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('frontend/icons/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('frontend/icons/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/icons/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-title" content="TCADM">
    <link rel="manifest" href="{{ asset('frontend/icons/site.webmanifest') }}">
</head>
<body>
<div class="layout">
    {{-- HEADER / NAV PRINCIPAL --}}
    <header class="topbar">
        <div class="brand">
            <div class="brand-logo green">
                <img src="{{ asset('frontend/icons/favicon.svg') }}" alt="Logo" class="brand-logo-img">
            </div>
            <div>
                <div class="brand-title">Ctrl+Alt+Del Motors | Panel Recepción</div>
                <div class="brand-sub">Bienvenido, {{ Auth::user()->name }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">Cerrar sesión</button>
        </form>
    </header>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="main">

        {{-- KPIs --}}
        <section class="kpi-grid">
            <article class="kpi-card">
                <h3>Total de citas del día</h3>
                <div class="kpi">{{ $stats['total'] ?? 0 }}</div>
                <div class="sub">{{ $fecha }}</div>
            </article>

            <article class="kpi-card">
                <h3>Resultados filtrados</h3>
                <div class="kpi" id="kpiFiltrados">{{ $stats['filtered'] ?? 0 }}</div>
                <div class="sub">Según filtros actuales</div>
            </article>

            <article class="kpi-card">
                <h3>Pendientes</h3>
                <div class="kpi">{{ $stats['pending'] ?? 0 }}</div>
                <div class="sub">Por atender</div>
            </article>

            <article class="kpi-card">
                <h3>Completadas</h3>
                <div class="kpi">{{ $stats['completed'] ?? 0 }}</div>
                <div class="sub">Servicios finalizados</div>
            </article>
        </section>

        {{-- FILTROS --}}
        <section class="filtros">
            <form method="GET" action="{{ route('recepcion.dashboard') }}" id="formFiltros" class="filtros-grid">
                <div class="campo">
                    <label for="searchCliente">Buscar cliente</label>
                    <input type="text" id="searchCliente" placeholder="Nombre del cliente">
                </div>

                <div class="campo">
                    <label for="searchFecha">Fecha</label>
                    <input
                        type="date"
                        id="searchFecha"
                        name="fecha"
                        value="{{ $fecha }}"
                    >
                </div>

                <div class="campo">
                    <label for="searchMecanico">Mecánico</label>
                    <select id="searchMecanico">
                        <option value="">Todos</option>
                        @foreach($mecanicos as $mecanico)
                            <option value="{{ $mecanico->user->name }}">
                                {{ $mecanico->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="campo">
                    <label for="searchEstatus">Estatus</label>
                    <select id="searchEstatus">
                        <option value="">Todos</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Confirmada">Confirmada</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Completada">Completada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                </div>

                <div class="campo campo-acciones">
                    <label>&nbsp;</label>
                    <button type="button" id="btnClear">Limpiar filtros</button>
                </div>
            </form>
        </section>

        {{-- TABLA DE CITAS --}}
        <section class="tabla-citas">
            <div class="tabla-header">
                <h2>Resultados</h2>
                <span id="labelResultados">Mostrando {{ $stats['filtered'] ?? 0 }} citas</span>
            </div>

            <div class="tabla-wrapper">
                <table>
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Vehículo</th>
                        <th>Servicio</th>
                        <th>Mecánico</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tableBody">
                    @forelse($citas as $cita)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($cita->fecha)->format('Y-m-d') }}</td>
                            <td>{{ \Carbon\Carbon::parse($cita->hora_inicio)->format('H:i') }}</td>
                            <td>{{ $cita->cliente->user->name ?? '—' }}</td>
                            <td>
                                @if($cita->vehiculo)
                                    {{ $cita->vehiculo->marca }}
                                    {{ $cita->vehiculo->modelo }}
                                    {{ $cita->vehiculo->anio }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @php
                                    $nombresServicios = $cita->servicios->pluck('nombre')->implode(', ');
                                @endphp
                                {{ $nombresServicios ?: '—' }}
                            </td>
                            <td>{{ $cita->mecanico?->user?->name ?? 'Sin asignar' }}</td>
                            <td>
                                @php
                                    $estatus = $cita->estatus; // pendiente, confirmada, en_proceso, completada, cancelada
                                    $label = $cita->estatus_texto ?? ucfirst(str_replace('_', ' ', $estatus));
                                @endphp
                                <span class="badge badge-{{ $estatus }}">{{ $label }}</span>
                            </td>
                            <td class="acciones">
                                @if ($cita->estatus === 'pendiente')
                                    <button
                                        type="button"
                                        class="btn-accion btn-confirmar"
                                        data-cita-id="{{ $cita->id }}"
                                        data-fecha="{{ $cita->fecha->format('Y-m-d') }}"
                                    >
                                        Confirmar
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-accion btn-cancelar"
                                        data-cita-id="{{ $cita->id }}"
                                    >
                                        Cancelar
                                    </button>
                                @elseif ($cita->estatus === 'confirmada')
                                    <button
                                        type="button"
                                        class="btn-accion btn-cancelar"
                                        data-cita-id="{{ $cita->id }}"
                                    >
                                        Cancelar
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No hay citas para esta fecha.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

{{-- Rutas JS para AJAX --}}
<script>
    window.APP_ROUTES = {
        citasPorFecha: "{{ route('recepcion.citas.fecha') }}",
        mecanicosDisponibles: "{{ route('recepcion.citas.mecanicos-disponibles', ':id') }}",
        asignarConfirmar: "{{ route('recepcion.citas.asignar-confirmar', ':id') }}",
        cancelarCita: "{{ route('recepcion.citas.cancelar', ':id') }}",
    };
</script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- JS del panel de recepción --}}
<script src="{{ asset('frontend/Panel-Recepcionista/script.js') }}"></script>
</body>
</html>
