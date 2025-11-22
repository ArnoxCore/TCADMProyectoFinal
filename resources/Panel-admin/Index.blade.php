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
          <div class="brand-sub">Bienvenido, Admin</div>
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
    <!-- KPIs -->
    <section class="cards-3">
      <article class="card">
        <div class="head"><span class="title">Total Citas</span></div>
        <div class="body">
          <div class="kpi">—</div>
          <div class="sub"><!-- backend: mostrar citas completadas --></div>
        </div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Tasa Completadas</span></div>
        <div class="body">
          <div class="kpi">—%</div>
          <div class="sub"><!-- backend: porcentaje de eficiencia --></div>
        </div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Por Asignar</span></div>
        <div class="body">
          <div class="kpi">—</div>
          <div class="sub"><!-- backend: número de citas sin mecánico --></div>
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
          <div>Mecánico</div>
          <div>Estatus</div>
          <div class="text-right">Acciones</div>
        </header>

        <!-- Ejemplo de fila (para que los backends repliquen dinámicamente) -->
        <div class="row">
          <div>—</div>
          <div>—</div>
          <div>—</div>
          <div>—</div>
          <div class="muted">Sin asignar</div>
          <div><span class="badge amber">Pendiente</span></div>
          <div class="text-right">
            <button class="button primary" data-open="#modalAsignar">Asignar</button>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Modal Asignar Mecánico -->
  <div class="modal-backdrop" id="modalAsignar">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="mTitle">
      <div class="m-head">
        <div id="mTitle" class="m-title">Asignar Mecánico</div>
        <div class="m-sub">Selecciona un mecánico para la cita</div>
      </div>
      <div class="m-body">
        <label class="label" for="mecanico">Mecánico</label>
        <select id="mecanico" class="select">
          <option value="">Selecciona un mecánico</option>
          <option>Diego Cardona</option>
          <option>Carlos Ruiz</option>
          <option>Aarón Emmanuel</option>
          <option>Jorge Avendaño</option>
        </select>

        <label class="label" for="nota">Nota (opcional)</label>
        <textarea id="nota" class="textarea" placeholder="Agregar comentario..."></textarea>
      </div>
      <div class="m-footer">
        <button class="button" data-close>Cancelar</button>
        <button class="button primary" data-close>Asignar</button>
      </div>
    </div>
  </div>

  <script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
</body>
</html>