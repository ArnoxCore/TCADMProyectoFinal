<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — Estadísticas</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('frontend/Panel-admin/admin.css') }}" />

  <!-- ====== FAVICON / PWA ====== -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('frontend/icons/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('frontend/icons/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('frontend/icons/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/icons/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-title" content="TCADM">
    <link rel="manifest" href="{{ asset('frontend/icons/site.webmanifest') }}">
</head>
<body data-page="estadisticas">
  <header class="header">
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
        <a href="{{ route('admin.personal') }}" data-nav="personal">Gestión de Personal</a>
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
    @php
      $inicio = $periodoSeleccionado['inicio'] ?? now()->startOfMonth()->toDateString();
      $fin = $periodoSeleccionado['fin'] ?? now()->endOfMonth()->toDateString();
      $metrics = $attendanceMetrics ?? [];
    @endphp

    <section class="section">
      <form method="GET" class="card" style="padding:16px; display:grid; gap:12px;">
        <div class="head" style="display:flex; justify-content:space-between; align-items:center;">
          <div class="title">Filtrar periodo</div>
          <button type="submit" class="button primary" style="padding:6px 14px;">Aplicar</button>
        </div>
        <div class="grid-2">
          <label class="label">Desde
            <input type="date" name="desde" class="input" value="{{ request('desde', $inicio) }}" />
          </label>
          <label class="label">Hasta
            <input type="date" name="hasta" class="input" value="{{ request('hasta', $fin) }}" />
          </label>
        </div>
        <label class="label">Tolerancia de puntualidad (min)
          <input type="number" min="1" name="tolerancia" class="input" value="{{ request('tolerancia', $metrics['tolerancia'] ?? 10) }}" />
        </label>
      </form>
    </section>

    <section class="cards-3">
      <article class="card">
        <div class="head"><span class="title">Asistencia</span></div>
        <div class="body">
          @if($metrics['camposDisponibles'] ?? false)
            <div class="kpi">{{ number_format($metrics['asistenciaPorc'] ?? 0, 1) }}%</div>
            <div class="sub">{{ $metrics['asistieron'] ?? 0 }} de {{ $metrics['total'] ?? 0 }} citas asistieron</div>
          @else
            <div class="kpi" style="color:var(--muted)">N/D</div>
            <div class="sub">Aún no se agregan los campos de asistencia</div>
          @endif
        </div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Puntualidad</span></div>
        <div class="body">
          @if(($metrics['camposDisponibles'] ?? false) && !is_null($metrics['puntualidadPorc'] ?? null))
            <div class="kpi">{{ number_format($metrics['puntualidadPorc'], 1) }}%</div>
            <div class="sub">{{ $metrics['registradasPuntualidad'] }} citas con hora registrada · tolerancia {{ $metrics['tolerancia'] ?? 10 }} min</div>
          @elseif($metrics['camposDisponibles'] ?? false)
            <div class="kpi" style="color:var(--muted)">—</div>
            <div class="sub">Aún no hay citas con horario real registrado</div>
          @else
            <div class="kpi" style="color:var(--muted)">N/D</div>
            <div class="sub">La migración de puntualidad no se ha aplicado</div>
          @endif
        </div>
      </article>
      <article class="card">
        <div class="head"><span class="title">No shows / Retraso</span></div>
        <div class="body">
          @if($metrics['camposDisponibles'] ?? false)
            <div class="kpi" style="color:var(--danger)">{{ $metrics['noShows'] ?? 0 }}</div>
            <div class="sub">Citas sin asistencia en el periodo</div>
            <div style="margin-top:12px;font-size:13px;color:var(--muted);">Promedio retraso: <strong>{{ number_format($metrics['promedioRetraso'] ?? 0, 1) }} min</strong></div>
          @else
            <div class="kpi" style="color:var(--muted)">N/D</div>
            <div class="sub">Esperando campos de asistencia/puntualidad</div>
          @endif
        </div>
      </article>
    </section>

    <section class="grid-2">
      <article class="card chart-card">
        <div class="head"><div class="title">Distribución de Estatus</div><span class="small muted">{{ \Illuminate\Support\Carbon::parse($inicio)->format('d/m/Y') }} - {{ \Illuminate\Support\Carbon::parse($fin)->format('d/m/Y') }}</span></div>
        <div class="body">
          <div class="canvas-wrap">
            <canvas id="statusPieChart" data-chart='@json($chartData ?? [])'></canvas>
          </div>
        </div>
      </article>

      <article class="card">
        <div class="head"><div class="title">Resumen del periodo</div></div>
        <div class="body">
          <div class="row" style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:8px 0">
            <span class="muted">Total de Citas</span>
            <strong>{{ number_format($resumenPeriodo['total'] ?? 0) }}</strong>
          </div>
          <div class="row" style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:8px 0">
            <span class="muted">Citas Completadas</span>
            <strong style="color:var(--success)">{{ number_format($resumenPeriodo['completadas'] ?? 0) }}</strong>
          </div>
          <div class="row" style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:8px 0">
            <span class="muted">Citas Pendientes</span>
            <strong style="color:var(--warning)">{{ number_format($resumenPeriodo['pendientes'] ?? 0) }}</strong>
          </div>
          <div class="row" style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:8px 0">
            <span class="muted">Citas Confirmadas</span>
            <strong style="color:var(--info)">{{ number_format($resumenPeriodo['confirmadas'] ?? 0) }}</strong>
          </div>
          <div class="row" style="display:flex;justify-content:space-between;padding:8px 0">
            <span class="muted">Citas Canceladas</span>
            <strong style="color:var(--danger)">{{ number_format($resumenPeriodo['canceladas'] ?? 0) }}</strong>
          </div>
        </div>
      </article>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
</body>
</html>