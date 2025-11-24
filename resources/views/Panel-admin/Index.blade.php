<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — Asignar Mecánicos</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('frontend/Panel-admin/admin.css') }}" />
</head>
<body data-page="index">
  <header class="header">
    <!-- encabezado común -->
    <div class="wrap">
      <div class="brand">
        <div class="brand-logo" aria-hidden="true"></div>
        <div>
          <div class="brand-title">Panel de Administración</div>
          <div class="brand-sub">Bienvenido, {{ Auth::user()->name ?? 'Admin' }}</div>
        </div>
      </div>
      <nav class="nav">
        <a href="{{ route('admin.dashboard') }}" data-nav="index">Asignar Mecánicos</a>
        <a href="{{ route('admin.servicios') }}" data-nav="servicios">Servicios</a>
        <a href="{{ route('admin.reportes') }}" data-nav="reportes">Reportes</a>
        <a href="{{ route('admin.estadisticas') }}" data-nav="estadisticas">Estadísticas</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="button ghost">Cerrar sesión</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="main">
    @if(session('success'))
      <div style="background:#dcfce7;color:#065f46;padding:12px 16px;border-radius:8px;border:1px solid #86efac;">
        {{ session('success') }}
      </div>
    @endif

    @if(session('error'))
      <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:8px;border:1px solid #fecaca;">
        {{ session('error') }}
      </div>
    @endif

    @if($errors->any())
      <div style="background:#fff7ed;color:#9a3412;padding:12px 16px;border-radius:8px;border:1px solid #fed7aa;">
        <strong>Revisa el formulario:</strong>
        <ul style="margin:8px 0 0 18px;">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <!-- KPIs -->
    <section class="cards-3">
      <article class="card">
        <div class="head"><span class="title">Total Citas</span></div>
        <div class="body">
          <div class="kpi">{{ number_format($totalCitas ?? 0) }}</div>
          <div class="sub">Registradas históricamente</div>
        </div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Tasa Completadas</span></div>
        <div class="body">
          <div class="kpi">{{ number_format($tasaCompletadas ?? 0, 1) }}%</div>
          <div class="sub">Porcentaje de citas cerradas</div>
        </div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Por Asignar</span></div>
        <div class="body">
          <div class="kpi">{{ number_format($porAsignar ?? 0) }}</div>
          <div class="sub">Citas esperando confirmación</div>
        </div>
      </article>
    </section>

    <!-- Tabla de citas para asignar -->
    <section class="section">
      <div class="section-head">
        <div>
          <h3 style="margin:0;font-size:16px">Asignar Mecánicos a Citas</h3>
          <p class="section-desc">Gestiona las asignaciones de mecánicos</p>
        </div>
      </div>

      <div class="table cols-7">
        <header>
          <div>Fecha</div>
          <div>Hora</div>
          <div>Cliente</div>
          <div>Servicio</div>
          <div>Vehículo</div>
          <div>Estatus</div>
          <div class="text-right">Acciones</div>
        </header>

        @forelse($citasPendientes as $cita)
          @php
            $clienteNombre = optional(optional($cita->cliente)->user)->name ?? 'Cliente sin nombre';
            $serviciosTexto = $cita->servicios->pluck('nombre')->filter()->implode(', ');
            $serviciosTexto = $serviciosTexto ?: 'Servicios no definidos';
            $vehiculoTexto = $cita->vehiculo ? trim(($cita->vehiculo->marca ?? '') . ' ' . ($cita->vehiculo->modelo ?? '')) : 'Sin vehículo registrado';
            $fechaTexto = $cita->fecha ? \Illuminate\Support\Carbon::parse($cita->fecha)->format('d/m/Y') : '—';
            $horaTexto = $cita->hora_inicio ? \Illuminate\Support\Carbon::parse($cita->hora_inicio)->format('H:i') : '—';
            $mecanicoNombre = optional(optional($cita->mecanico)->user)->name ?? 'Sin asignar';
            $badgeClass = match($cita->estatus) {
              'pendiente' => 'badge amber',
              'confirmada','en_proceso' => 'badge blue',
              'completada' => 'badge green',
              default => 'badge'
            };
          @endphp
          <div class="row">
            <div>{{ $fechaTexto }}</div>
            <div>{{ $horaTexto }}</div>
            <div>{{ $clienteNombre }}</div>
            <div>{{ $serviciosTexto }}</div>
            <div class="muted">{{ $vehiculoTexto }}</div>
            <div><span class="{{ $badgeClass }}">{{ ucfirst($cita->estatus) }}</span></div>
            <div class="text-right">
              <button type="button"
                      class="button primary btn-asignar-cita"
                      data-open="#modalAsignar"
                      data-cita-id="{{ $cita->id }}"
                      data-cita-cliente="{{ e($clienteNombre) }}"
                      data-cita-servicio="{{ e($serviciosTexto) }}"
                      data-cita-vehiculo="{{ e($vehiculoTexto) }}"
                      data-cita-fecha="{{ $fechaTexto }}"
                      data-cita-hora="{{ $horaTexto }}"
                      @if(($mecanicosDisponibles ?? collect())->isEmpty()) title="No hay mecánicos con disponibilidad" @endif>
                Asignar
              </button>
            </div>
          </div>
        @empty
          <div class="row" style="grid-column:1 / -1; text-align:center; color:#6b7280;">
            No hay citas pendientes por confirmar.
          </div>
        @endforelse
      </div>
    </section>
  </main>

  <!-- Modal Asignar Mecánico -->
  <div class="modal-backdrop" id="modalAsignar">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="mTitle">
      <form method="POST" id="formAsignarCita" data-action-template="{{ url('/admin/citas') }}/__ID__/asignar">
        @csrf
        @method('PATCH')
        <div class="m-head">
          <div id="mTitle" class="m-title">Asignar Mecánico</div>
          <div class="m-sub">Confirma la cita y notifica al mecánico seleccionado</div>
        </div>
        <div class="m-body">
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;display:grid;gap:4px;">
            <div class="small muted">Cliente</div>
            <div id="asignarCliente" style="font-weight:600;">—</div>
            <div class="small muted" style="margin-top:8px;">Servicio / Vehículo</div>
            <div id="asignarServicio">—</div>
            <div class="small muted" style="margin-top:8px;">Fecha y hora</div>
            <div id="asignarFecha">—</div>
          </div>

          <label class="label" for="mecanicoSelect">Mecánico</label>
          <select id="mecanicoSelect" name="mecanico_id" class="select" required @if(($mecanicosDisponibles ?? collect())->isEmpty()) disabled @endif>
            <option value="">Selecciona un mecánico</option>
            @forelse($mecanicosDisponibles as $mecanico)
              <option value="{{ $mecanico->id }}">
                {{ $mecanico->user->name ?? ('Mecánico #'.$mecanico->numero_empleado) }}
                — {{ $mecanico->citas_activas_count ?? 0 }}/4 citas activas
              </option>
            @empty
              <option value="" disabled>No hay mecánicos con disponibilidad</option>
            @endforelse
          </select>
          @if(($mecanicosDisponibles ?? collect())->isEmpty())
            <p class="small" style="color:#b45309;margin:4px 0 0;">Todos los mecánicos alcanzaron el máximo de citas permitidas.</p>
          @endif
        </div>
        <div class="m-footer">
          <button type="button" class="button" data-close>Cancelar</button>
          <button type="submit" class="button primary" @if(($mecanicosDisponibles ?? collect())->isEmpty()) disabled @endif>Asignar</button>
        </div>
      </form>
    </div>
  </div>

  <script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('formAsignarCita');
      const clienteEl = document.getElementById('asignarCliente');
      const servicioEl = document.getElementById('asignarServicio');
      const fechaEl = document.getElementById('asignarFecha');
      const mecanicoSelect = document.getElementById('mecanicoSelect');

      document.querySelectorAll('.btn-asignar-cita').forEach(btn => {
        btn.addEventListener('click', () => {
          if (!form || !form.dataset.actionTemplate) return;
          const actionTemplate = form.dataset.actionTemplate;
          form.action = actionTemplate.replace('__ID__', btn.dataset.citaId);

          clienteEl.textContent = btn.dataset.citaCliente || '—';
          const vehiculo = btn.dataset.citaVehiculo || '';
          const servicio = btn.dataset.citaServicio || '—';
          servicioEl.textContent = vehiculo ? `${servicio} · ${vehiculo}` : servicio;

          const fecha = btn.dataset.citaFecha || '—';
          const hora = btn.dataset.citaHora || '';
          fechaEl.textContent = hora ? `${fecha} · ${hora}` : fecha;

          if (mecanicoSelect) {
            mecanicoSelect.value = '';
          }
        });
      });
    });
  </script>
</body>
</html>