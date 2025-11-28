<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — Reportes</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('frontend/Panel-admin/admin.css') }}" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" />

  <!-- ====== FAVICON / PWA ====== -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('frontend/icons/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('frontend/icons/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('frontend/icons/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/icons/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-title" content="TCADM">
    <link rel="manifest" href="{{ asset('frontend/icons/site.webmanifest') }}">
</head>
<body data-page="reportes">
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
        <a href="{{ route('admin.perfil') }}" data-nav="perfil">Mis datos</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="button ghost">Cerrar sesión</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="main">
    @if(!empty($rangeError))
      <div data-toast="error" data-message="{{ $rangeError }}" style="display:none"></div>
    @endif

    @php
      $inicio = $periodoSeleccionado['inicio'] ?? now()->startOfMonth()->toDateString();
      $fin = $periodoSeleccionado['fin'] ?? now()->endOfMonth()->toDateString();
      $labels = collect($chartData['labels'] ?? []);
      $hasData = $labels->count() > 0;
    @endphp

    <section class="section">
      <form method="GET" class="card" style="padding:16px; display:grid; gap:12px;">
        <div class="head" style="display:flex; justify-content:space-between; align-items:center;">
          <div class="title">Filtrar periodo</div>
          <button type="submit" class="button primary" style="padding:6px 14px;">Actualizar</button>
        </div>
        <div class="grid-2">
          <label class="label">Desde
            <input type="date" name="desde" class="input" value="{{ request('desde', $inicio) }}">
          </label>
          <label class="label">Hasta
            <input type="date" name="hasta" class="input" value="{{ request('hasta', $fin) }}">
          </label>
        </div>
      </form>
    </section>

    <section class="card chart-card">
      <div class="head">
        <div>
          <div class="title">Servicios más solicitados</div>
          <p class="section-desc">
            @if($hasData)
              Período {{ \Illuminate\Support\Carbon::parse($inicio)->format('d/m/Y') }} – {{ \Illuminate\Support\Carbon::parse($fin)->format('d/m/Y') }}
            @else
              Aún no hay citas con servicios en este rango.
            @endif
          </p>
        </div>
      </div>
      <div class="body">
        <div class="canvas-wrap">
          <canvas id="servicesBarChart" data-chart='@json($chartData ?? [])'></canvas>
        </div>
      </div>
    </section>

    @if($hasData)
      <section class="cards-3">
        <article class="card">
          <div class="head"><span class="title">Servicio Top</span></div>
          <div class="body">
            <div class="kpi">{{ $topService->nombre ?? 'N/D' }}</div>
            <div class="sub">{{ $topService->total ?? 0 }} solicitudes</div>
          </div>
        </article>
        <article class="card">
          <div class="head"><span class="title">Total de servicios</span></div>
          <div class="body">
            <div class="kpi">{{ number_format($totalSolicitudes ?? 0) }}</div>
            <div class="sub">Citas registradas en el periodo</div>
          </div>
        </article>
        <article class="card">
          <div class="head"><span class="title">Servicios mostrados</span></div>
          <div class="body">
            <div class="kpi">{{ $labels->count() }}</div>
            <div class="sub">Hasta 8 servicios con más demanda</div>
          </div>
        </article>
      </section>
    @endif
  </main>

  <!-- Librería y scripts listos, pero sin inicializar -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
  <script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
</body>
</html>