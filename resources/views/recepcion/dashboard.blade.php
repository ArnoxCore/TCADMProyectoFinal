<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Recepción - Ctrl+Alt+Del Motors</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF para peticiones AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- CSS del panel de recepción --}}
    <link rel="stylesheet" href="{{ asset('frontend/Panel-Recepcionista/recepcion.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">

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
                <div class="sub">
                    @if(!empty($showAll) && $showAll)
                        Todas las fechas
                    @elseif(!empty($fecha_inicio) && !empty($fecha_fin))
                        {{ $fecha_inicio }} a {{ $fecha_fin }}
                    @elseif(!empty($fecha_inicio))
                        Desde {{ $fecha_inicio }}
                    @elseif(!empty($fecha_fin))
                        Hasta {{ $fecha_fin }}
                    @else
                        Sin rango definido
                    @endif
                </div>
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
            <form method="GET"
                  action="{{ route('recepcion.dashboard') }}"
                  id="formFiltros"
                  class="filtros-grid">

                <div class="campo">
                    <label for="searchCliente">Buscar cliente</label>
                    <input type="text" id="searchCliente" placeholder="Nombre del cliente">
                </div>

                <div class="campo rango-fechas">
                    <label>Rango de fechas</label>
                    <div class="rango-fechas__inputs">
                        <input
                            type="date"
                            id="searchFechaInicio"
                            name="fecha_inicio"
                            value="{{ request('fecha_inicio', $fecha_inicio ?? '') }}"
                        >
                        <span class="rango-fechas__divider">a</span>
                        <input
                            type="date"
                            id="searchFechaFin"
                            name="fecha_fin"
                            value="{{ request('fecha_fin', $fecha_fin ?? '') }}"
                        >
                        <button type="button" class="rango-fechas__today" id="btnToday">Hoy</button>
                    </div>
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
                    <button type="button"
                            id="btnClear"
                            class="btn-clear"
                            title="Limpiar filtros"
                            aria-label="Limpiar filtros">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <g fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7" />
                                <path d="M10 11v6" />
                                <path d="M14 11v6" />
                                <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" />
                                <path d="M4 7h16" />
                            </g>
                        </svg>
                    </button>
                </div>

                {{-- Modo "ver todas" o "solo por fecha" --}}
                  <input type="hidden"
                      name="show_all"
                      id="show_all"
                      value="{{ !empty($showAll) && $showAll ? 1 : 0 }}">

                  <input type="hidden"
                      id="rangeApplied"
                      name="range_applied"
                      value="{{ !empty($range_applied) ? 1 : 0 }}">
            </form>
        </section>

        <section class="view-switcher">
            <span>Vista:</span>
            <div class="view-switcher__buttons">
                <button type="button" class="view-toggle active" data-target="tableView">Tabla</button>
                <button type="button" class="view-toggle" data-target="calendarView">Calendario</button>
            </div>
        </section>

        {{-- TABLA DE CITAS --}}
        <section class="tabla-citas" id="tableView">
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
                        <th>Asistencia</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody id="tableBody">
                    @forelse($citas as $cita)
                        <tr data-cita-id="{{ $cita->id }}">
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
                                <div class="attendance-state">
                                    <span>{{ $cita->attendance_label }}</span>
                                    @if($cita->attendance_secondary)
                                        <small>{{ $cita->attendance_secondary }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @php
                                    $estatus = $cita->estatus; // pendiente, confirmada, en_proceso, completada, cancelada
                                    $label = $cita->estatus_texto ?? ucfirst(str_replace('_', ' ', $estatus));
                                @endphp
                                <span class="badge badge-{{ $estatus }}">{{ $label }}</span>
                            </td>
                            <td class="acciones">
                                <div class="acciones-group">
                                @php
                                    $canCheckIn = $cita->canCheckIn();
                                    $canStart = $cita->canStartService();
                                    $canNoShow = $cita->canMarkNoShow();
                                    $canCancel = $cita->canCancelDesdeRecepcion();
                                @endphp

                                @if ($canCheckIn)
                                    <button
                                        type="button"
                                        class="btn-accion btn-icon btn-checkin"
                                        data-cita-id="{{ $cita->id }}"
                                        title="Registrar llegada"
                                        aria-label="Registrar llegada"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M5 12l4 4 10-10" />
                                        </svg>
                                    </button>
                                @endif

                                @if ($canStart)
                                    <button
                                        type="button"
                                        class="btn-accion btn-icon btn-start"
                                        data-cita-id="{{ $cita->id }}"
                                        title="Iniciar servicio"
                                        aria-label="Iniciar servicio"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M8 5v14l10-7z" />
                                        </svg>
                                    </button>
                                @endif

                                @if ($canNoShow)
                                    <button
                                        type="button"
                                        class="btn-accion btn-icon btn-no-show"
                                        data-cita-id="{{ $cita->id }}"
                                        title="Marcar inasistencia"
                                        aria-label="Marcar inasistencia"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="9" />
                                            <path d="M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif

                                @if ($canCancel)
                                    <button
                                        type="button"
                                        class="btn-accion btn-icon btn-cancelar"
                                        data-cita-id="{{ $cita->id }}"
                                        title="Cancelar cita"
                                        aria-label="Cancelar cita"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M6 6l12 12M18 6l-12 12" />
                                        </svg>
                                    </button>
                                @endif

                                @if (! $canCheckIn && ! $canStart && ! $canNoShow && ! $canCancel)
                                    <span class="acciones-placeholder">—</span>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">No hay citas para esta fecha.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación Laravel: SOLO "Anterior" y "Siguiente" --}}
            @if($citas->hasPages())
                @php
                    // Mantener todos los parámetros de la URL (fecha, show_all, etc.) menos "page"
                    $paginated = $citas->appends(request()->except('page'));
                @endphp

                <div class="tabla-pagination">
                    {{-- Anterior --}}
                    @if ($paginated->onFirstPage())
                        <span aria-disabled="true">« Anterior</span>
                    @else
                        <a href="{{ $paginated->previousPageUrl() }}">« Anterior</a>
                    @endif

                    {{-- Siguiente --}}
                    @if ($paginated->hasMorePages())
                        <a href="{{ $paginated->nextPageUrl() }}">Siguiente »</a>
                    @else
                        <span aria-disabled="true">Siguiente »</span>
                    @endif
                </div>
            @endif
        </section>

        {{-- CALENDARIO DE CITAS --}}
        <section class="calendar-section" id="calendarView" style="display:none;">
            <div class="tabla-header">
                <h2>Calendario</h2>
                <span>Visualiza disponibilidad y evita traslapes</span>
            </div>
            <div class="calendar-wrapper">
                <div id="recepcionCalendar"></div>
            </div>
        </section>
    </main>
</div>

{{-- Rutas JS para AJAX --}}
<script>
    window.APP_ROUTES = {
        citasPorFecha: "{{ route('recepcion.citas.fecha') }}",
        cancelarCita: "{{ route('recepcion.citas.cancelar', ['cita' => '__ID__']) }}",
        checkIn: "{{ route('recepcion.citas.check-in', ['cita' => '__ID__']) }}",
        startService: "{{ route('recepcion.citas.start-service', ['cita' => '__ID__']) }}",
        noShow: "{{ route('recepcion.citas.no-show', ['cita' => '__ID__']) }}",
        calendarEvents: "{{ route('recepcion.citas.calendario') }}",
    };
</script>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- FullCalendar --}}
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

{{-- JS del panel de recepción --}}
<script src="{{ asset('frontend/Panel-Recepcionista/script.js') }}"></script>
</body>
</html>
