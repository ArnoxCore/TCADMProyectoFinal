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
    <section class="grid-2">
      <article class="card chart-card">
        <div class="head"><div class="title">Distribución de Estatus</div></div>
        <div class="body">
          <div class="canvas-wrap">
            <canvas id="statusPieChart" data-chart='@json($chartData ?? [])'></canvas>
          </div>
        </div>
      </article>

      <article class="card">
        <div class="head"><div class="title">Resumen general del mes</div></div>
        <div class="body">
          <div class="row" style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:8px 0">
            <span class="muted">Total de Citas</span>
            <strong>{{ number_format($resumenMes['total'] ?? 0) }}</strong>
          </div>
          <div class="row" style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:8px 0">
            <span class="muted">Citas Completadas</span>
            <strong style="color:var(--success)">{{ number_format($resumenMes['completadas'] ?? 0) }}</strong>
          </div>
          <div class="row" style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:8px 0">
            <span class="muted">Citas Pendientes</span>
            <strong style="color:var(--warning)">{{ number_format($resumenMes['pendientes'] ?? 0) }}</strong>
          </div>
          <div class="row" style="display:flex;justify-content:space-between;border-bottom:1px solid var(--line);padding:8px 0">
            <span class="muted">Citas Confirmadas</span>
            <strong style="color:var(--info)">{{ number_format($resumenMes['confirmadas'] ?? 0) }}</strong>
          </div>
          <div class="row" style="display:flex;justify-content:space-between;padding:8px 0">
            <span class="muted">Citas Canceladas</span>
            <strong style="color:var(--danger)">{{ number_format($resumenMes['canceladas'] ?? 0) }}</strong>
          </div>
        </div>
      </article>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
</body>
</html>