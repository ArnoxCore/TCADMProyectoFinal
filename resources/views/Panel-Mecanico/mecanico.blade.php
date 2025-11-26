<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel de Mecánico</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('frontend/Panel-Mecanico/style.css') }}">

</head>
<body data-page="mecanico">
@php use App\Models\Cita as CitaModel; @endphp
  <header class="header">
    <div class="wrap">
      <div class="brand">
        <div class="brand-logo mechanic" aria-hidden="true"></div>
        <div>
          <div class="brand-title">Panel de Mecánico</div>
          <div class="brand-sub">Bienvenido, {{ (isset($mecanico) && $mecanico && $mecanico->user) ? $mecanico->user->name : Auth::user()->name }}</div>
        </div>
      </div>
      <nav class="nav">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="button ghost">Cerrar sesión</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="main">
    <!-- Tarjetas de resumen -->
    <section class="cards-4">
      <article class="card">
        <div class="head"><span class="title">Citas Hoy</span></div>
        <div class="body"><div class="kpi">{{ $citasHoyCount ?? 0 }}</div></div>
      </article>
      <article class="card">
        <div class="head"><span class="title">En Proceso</span></div>
        <div class="body"><div class="kpi">{{ $enProcesoCount ?? 0 }}</div></div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Completadas</span></div>
        <div class="body"><div class="kpi">{{ $completadasCount ?? 0 }}</div></div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Pendientes</span></div>
        <div class="body"><div class="kpi">{{ $pendientesCount ?? 0 }}</div></div>
      </article>
    </section>

    <!-- Filtro -->
    <section class="section">
      <div class="section-head">
        <div>
          <h3 style="margin:0;font-size:16px">Filtrar Citas</h3>
          <p class="section-desc">Filtra las citas según la fecha</p>
        </div>
      </div>
      <div class="filter-row">
        <div class="filter-field">
          <label for="fecha">Fecha</label>
          <form method="GET" style="display:flex; gap:12px; align-items:flex-end;">
            <input type="date" id="fecha" name="fecha" class="input" value="{{ request('fecha') }}">
            <button type="submit" class="button">Filtrar</button>
            @if(request('fecha'))
              <a href="{{ url()->current() }}" class="button" style="text-decoration:none;">Limpiar</a>
            @endif
          </form>
        </div>
      </div>
    </section>

    <!-- Citas Filtradas por Fecha (si aplica) -->
    @if(request('fecha'))
      <section class="section">
        <div class="section-head">
          <h3 style="margin:0;font-size:16px">Citas del {{ \Carbon\Carbon::createFromFormat('Y-m-d', request('fecha'))->format('d/m/Y') }}</h3>
        </div>
        @if(isset($citasFiltradas) && $citasFiltradas->count())
          <div class="list-citas">
            @foreach($citasFiltradas as $cita)
              @php
                $serviciosStr = $cita->servicios->pluck('nombre')->join(', ');
                $vehiculoStr = $cita->vehiculo ? strtoupper(trim(($cita->vehiculo->marca ?? '') . ' ' . ($cita->vehiculo->modelo ?? ''))) : '';
                $fechaStr = $cita->fecha?->format('d/m/Y') ?? '';
                $horaStr = trim(($cita->hora_inicio ?? '') . ($cita->hora_fin ? ' - '.$cita->hora_fin : ''));
                $clienteNombre = $cita->cliente ? ($cita->cliente->user->name ?? 'Sin nombre') : 'Sin cliente';
                $clienteAsistio = $cita->asistio === true;
                $observacionesPayload = $cita->observaciones->map(function ($obs) {
                  $fecha = $obs->created_at
                    ? $obs->created_at->copy()->timezone(CitaModel::LOCAL_TIMEZONE)->format('d/m/Y H:i')
                    : null;
                  return [
                    'texto' => $obs->observacion,
                    'fecha' => $fecha,
                    'mecanico' => optional(optional($obs->mecanico)->user)->name,
                  ];
                })->values();
              @endphp
              <div class="cita-item" style="border:1px solid #eef2f6; padding:12px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                  <strong>{{ $cita->servicios->pluck('nombre')->join(', ') }}</strong>
                  <div style="font-size:13px; color:#555; margin-top:6px;">
                    {{ $cita->vehiculo ? strtoupper($cita->vehiculo->marca.' '.$cita->vehiculo->modelo) : '' }}
                    <br>
                    Hora: {{ $cita->hora_inicio ?? '' }} {{ $cita->hora_fin ? '- '.$cita->hora_fin : '' }}
                    <br>
                    Estado: <strong>{{ $cita->estatus_texto ?? $cita->estatus }}</strong>
                  </div>
                </div>
                <div style="text-align:right; min-width:140px;">
                  <div style="font-size:13px; color:#777;">Cliente: {{ $clienteNombre }}</div>
                  <div style="margin-top:8px;"><button type="button" class="button" onclick='abrirModalCita({{ $cita->id }}, @json($serviciosStr), @json($vehiculoStr), @json($fechaStr), @json($horaStr), @json($cita->estatus), {{ $cita->mecanico_id ?? 'null' }}, @json($observacionesPayload), @json($clienteNombre), @json($clienteAsistio))'>Ver</button></div>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="empty-card">
            <div class="icon-placeholder" aria-hidden="true">📅</div>
            <p>No hay citas para esta fecha</p>
          </div>
        @endif
      </section>
    @endif

    <!-- Citas de Hoy -->
    <section class="section">
      <div class="section-head">
        <h3 style="margin:0;font-size:16px">Citas Confirmadas</h3>
      </div>
      @if(isset($citasHoy) && $citasHoy->count())
        <div class="list-citas">
          @foreach($citasHoy as $cita)
            @php
              $esHoy = $cita->fecha && $cita->fecha->format('Y-m-d') === today()->format('Y-m-d');
              $serviciosStr = $cita->servicios->pluck('nombre')->join(', ');
              $vehiculoStr = $cita->vehiculo ? strtoupper(trim(($cita->vehiculo->marca ?? '') . ' ' . ($cita->vehiculo->modelo ?? ''))) : '';
              $fechaStr = $cita->fecha?->format('d/m/Y') ?? '';
              $horaStr = trim(($cita->hora_inicio ?? '') . ($cita->hora_fin ? ' - '.$cita->hora_fin : ''));
              $clienteNombre = $cita->cliente ? ($cita->cliente->user->name ?? 'Sin nombre') : 'Sin cliente';
              $clienteAsistio = $cita->asistio === true;
              $clienteAsistio = $cita->asistio === true;
              $observacionesPayload = $cita->observaciones->map(function ($obs) {
                $fecha = $obs->created_at
                  ? $obs->created_at->copy()->timezone(CitaModel::LOCAL_TIMEZONE)->format('d/m/Y H:i')
                  : null;
                return [
                  'texto' => $obs->observacion,
                  'fecha' => $fecha,
                  'mecanico' => optional(optional($obs->mecanico)->user)->name,
                ];
              })->values();
            @endphp
            <div class="cita-item" style="border:1px solid #eef2f6; padding:12px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; {{ $esHoy ? 'background:#fff9e6; border-left:4px solid #ffc107;' : '' }}">
              <div>
                <strong>{{ $cita->servicios->pluck('nombre')->join(', ') }}</strong>
                @if($esHoy)
                  <span style="background:#ffc107; color:#333; padding:2px 8px; border-radius:3px; font-size:11px; margin-left:8px;">HOY</span>
                @endif
                <div style="font-size:13px; color:#555; margin-top:6px;">
                  {{ $cita->vehiculo ? strtoupper($cita->vehiculo->marca.' '.$cita->vehiculo->modelo) : '' }}
                  <br>
                  Fecha: {{ $cita->fecha?->format('d/m/Y') ?? '' }}
                  &nbsp; Hora: {{ $cita->hora_inicio ?? '' }} {{ $cita->hora_fin ? '- '.$cita->hora_fin : '' }}
                  <br>
                  Estado: <strong>{{ $cita->estatus_texto ?? $cita->estatus }}</strong>
                </div>
              </div>
              <div style="text-align:right; min-width:140px;">
                <div style="font-size:13px; color:#777;">Cliente: {{ $clienteNombre }}</div>
                <div style="margin-top:8px;"><button type="button" class="button" onclick='abrirModalCita({{ $cita->id }}, @json($serviciosStr), @json($vehiculoStr), @json($fechaStr), @json($horaStr), @json($cita->estatus), {{ $cita->mecanico_id ?? 'null' }}, @json($observacionesPayload), @json($clienteNombre), @json($clienteAsistio))'>Ver</button></div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-card">
          <div class="icon-placeholder" aria-hidden="true">📅</div>
          <p>No hay citas pendientes</p>
        </div>
      @endif
    </section>

    <!-- Citas de la Semana -->
    <section class="section">
      <div class="section-head">
        <h3 style="margin:0;font-size:16px">Citas esta Semana</h3>
      </div>
      @if(isset($citasSemana) && $citasSemana->count())
        <div class="list-citas">
          @foreach($citasSemana as $cita)
            @php
              $serviciosStr = $cita->servicios->pluck('nombre')->join(', ');
              $vehiculoStr = $cita->vehiculo ? strtoupper(trim(($cita->vehiculo->marca ?? '') . ' ' . ($cita->vehiculo->modelo ?? ''))) : '';
              $fechaStr = $cita->fecha?->format('d/m/Y') ?? '';
              $horaStr = trim(($cita->hora_inicio ?? '') . ($cita->hora_fin ? ' - '.$cita->hora_fin : ''));
              $clienteNombre = $cita->cliente ? ($cita->cliente->user->name ?? 'Sin nombre') : 'Sin cliente';
              $observacionesPayload = $cita->observaciones->map(function ($obs) {
                $fecha = $obs->created_at
                  ? $obs->created_at->copy()->timezone(CitaModel::LOCAL_TIMEZONE)->format('d/m/Y H:i')
                  : null;
                return [
                  'texto' => $obs->observacion,
                  'fecha' => $fecha,
                  'mecanico' => optional(optional($obs->mecanico)->user)->name,
                ];
              })->values();
            @endphp
            <div class="cita-item" style="border:1px solid #eef2f6; padding:12px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
              <div>
                <strong>{{ $cita->servicios->pluck('nombre')->join(', ') }}</strong>
                <div style="font-size:13px; color:#555; margin-top:6px;">
                  {{ $cita->vehiculo ? strtoupper($cita->vehiculo->marca.' '.$cita->vehiculo->modelo) : '' }}
                  <br>
                  Fecha: {{ $cita->fecha?->format('d/m/Y') ?? '' }}
                  &nbsp; Hora: {{ $cita->hora_inicio ?? '' }} {{ $cita->hora_fin ? '- '.$cita->hora_fin : '' }}
                  <br>
                  Estado: <strong>{{ $cita->estatus_texto ?? $cita->estatus }}</strong>
                </div>
              </div>
              <div style="text-align:right; min-width:140px;">
                <div style="font-size:13px; color:#777;">Cliente: {{ $clienteNombre }}</div>
                <div style="margin-top:8px;"><button type="button" class="button" onclick='abrirModalCita({{ $cita->id }}, @json($serviciosStr), @json($vehiculoStr), @json($fechaStr), @json($horaStr), @json($cita->estatus), {{ $cita->mecanico_id ?? 'null' }}, @json($observacionesPayload), @json($clienteNombre), @json($clienteAsistio))'>Ver</button></div>
              </div>
            </div>
          @endforeach
        </div>
      @else
        <div class="empty-card">
          <div class="icon-placeholder" aria-hidden="true">📅</div>
          <p>No hay citas asignadas para esta semana</p>
        </div>
      @endif
    </section>
  </main>

  <script src="{{ asset('frontend/Panel-Mecanico/script.js') }}"></script>
  <script src="{{ asset('frontend/Panel-Mecanico/modal-cita.js') }}"></script>
  
  <!-- Modal de Detalles de Cita -->
  <div id="citaModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:white; padding:30px; border-radius:8px; max-width:500px; width:90%; max-height:80vh; overflow-y:auto; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 id="citaTitle" style="margin:0;">Detalles de Cita</h2>
        <button onclick="cerrarModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">×</button>
      </div>
      
      <form id="citaForm" onsubmit="return false;">
        <div style="margin-bottom:16px;">
          <label style="display:block; font-weight:600; margin-bottom:8px;">Servicio</label>
          <div id="citaServicio" style="background:#f5f5f5; padding:10px; border-radius:4px;"></div>
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-weight:600; margin-bottom:8px;">Vehículo</label>
          <div id="citaVehiculo" style="background:#f5f5f5; padding:10px; border-radius:4px;"></div>
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-weight:600; margin-bottom:8px;">Fecha y Hora</label>
          <div id="citaFechaHora" style="background:#f5f5f5; padding:10px; border-radius:4px;"></div>
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-weight:600; margin-bottom:8px;">Estado</label>
          <select id="citaEstatus" onchange="actualizarEstatus()" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
            <option value="en_proceso">En Proceso</option>
            <option value="completada">Completada</option>
          </select>
          <small id="estatusHelper" style="display:block; margin-top:6px; color:#777;">Recepción debe registrar la llegada del cliente para habilitar este cambio.</small>
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-weight:600; margin-bottom:8px;">Observaciones del mecánico</label>
          <textarea id="citaObservaciones" rows="3" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; resize:vertical;" placeholder="Describe hallazgos adicionales o recomendaciones"></textarea>
          <small style="color:#777;">Esta nota no cambia el estado de la cita ni la confirma.</small>
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-weight:600; margin-bottom:8px;">Historial de observaciones</label>
          <div id="observacionesHistorial" style="background:#f8f9fb; border:1px solid #e1e6ef; border-radius:4px; padding:10px; max-height:200px; overflow-y:auto;">
            <p style="margin:0; color:#777;">Sin observaciones registradas.</p>
          </div>
        </div>

        <div style="display:flex; gap:10px; margin-bottom:16px;">
          <button type="button" onclick="guardarObservacion()" id="btnGuardarObservacion" class="button primary" style="flex:1;">Guardar observación</button>
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-weight:600; margin-bottom:8px;">Cliente</label>
          <div id="citaCliente" style="background:#f5f5f5; padding:10px; border-radius:4px; min-height:38px;"></div>
        </div>

        <div style="display:flex; gap:10px;">
          <button type="button" onclick="cerrarModal()" class="button ghost" style="flex:1;">Cerrar</button>
          <button type="button" onclick="guardarCambios()" class="button primary" style="flex:1;">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
  <script>
    // Instancia global de Notyf
    window.toast = new Notyf({
        duration: 3500,
        position: {
            x: 'right',
            y: 'top',
        },
        dismissible: true
    });
</script>
<script>
    @if(session('success'))
    toast.success("{{ session('success') }}");
    @endif

    @if(session('error'))
    toast.error("{{ session('error') }}");
    @endif

    @if(session('warning'))
    toast.warning("{{ session('warning') }}");
    @endif

    @if(session('info'))
    toast.info("{{ session('info') }}");
    @endif
</script>

</body>
</html>