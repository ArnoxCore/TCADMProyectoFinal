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
</head>
<body data-page="reportes">
  <header class="header">
    <div class="wrap">
      <div class="brand">
        <div class="brand-logo" aria-hidden="true"></div>
        <div>
          <div class="brand-title">Panel de Administración</div>
          <div class="brand-sub">Bienvenido, Admin</div>
        </div>
      </div>
      <nav class="nav">
        <a href="{{ route('admin.dashboard') }}" data-nav="index">Asignar Mecánicos</a>
        <a href="{{ route('admin.servicios') }}" data-nav="servicios">Servicios</a>
        <a href="{{ route('admin.reportes') }}" data-nav="reportes">Reportes</a>
        <a href="#" data-nav="estadisticas">Estadísticas</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="button ghost">Cerrar sesión</button>
        </form>
      </nav>
    </div>
  </header>

  <main class="main">
    <section class="card chart-card">
      <div class="head">
        <div>
          <div class="title">Servicios Más Solicitados (mes)</div>
          <p class="section-desc">Gráfica pendiente — aún sin datos cargados</p>
        </div>
      </div>
      <div class="body">
        <div class="canvas-wrap">
          <!-- Canvas vacío para que el backend o JS lo llenen -->
          <canvas id="servicesBarChart"></canvas>
        </div>
      </div>
    </section>
  </main>

  <!-- Librería y scripts listos, pero sin inicializar -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
</body>
</html>