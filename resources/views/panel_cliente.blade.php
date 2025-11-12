<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Panel del Cliente | Ctrl + Alt + Del Motors</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('frontend/Panel-cliente/cliente.css') }}">
</head>
<body data-page="cliente">
  <header class="header">
    <div class="wrap">
      <div class="brand">
        <div class="brand-logo client" aria-hidden="true"></div>
        <div>
          <div class="brand-title">Ctrl + Alt + Del Motors</div>
          <div class="brand-sub">Bienvenido, [Nombre del Cliente]</div>
        </div>
      </div>
      <button class="button ghost">Cerrar sesión</button>
    </div>
  </header>

  <main class="main">
    <!-- Botón principal -->
    <section class="section center">
      <button id="openModal" class="button primary">+ Agendar Nueva Cita</button>
    </section>

    <!-- Tarjetas de estadísticas -->
    <section class="cards-3">
      <article class="card">
        <div class="head"><span class="title">Próximas Citas</span></div>
        <div class="body"><div class="kpi">0</div><div class="sub">Citas agendadas</div></div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Servicios Completados</span></div>
        <div class="body"><div class="kpi">0</div><div class="sub">Total de servicios</div></div>
      </article>
      <article class="card">
        <div class="head"><span class="title">Próximo Servicio</span></div>
        <div class="body"><div class="kpi">—</div><div class="sub">Fecha estimada</div></div>
      </article>
    </section>

    <!-- Tabs -->
    <section class="section">
      <div class="tabs">
        <button class="tab active" data-tab="proximas">Próximas Citas</button>
        <button class="tab" data-tab="historial">Historial</button>
      </div>

      <div id="proximas" class="tab-content active">
        <article class="appointment">
          <header>
            <h3>Ejemplo Próxima Cita</h3>
            <span class="badge warning">Pendiente</span>
          </header>
          <ul class="appointment-info">
            <li>Detalles del vehículo</li>
            <li>Información del mecánico</li>
          </ul>
          <footer>
            <button class="button ghost small">Modificar</button>
            <button class="button ghost small">Cancelar</button>
          </footer>
        </article>
      </div>

      <div id="historial" class="tab-content">
        <article class="appointment">
          <header>
            <h3>Ejemplo Cita Completada</h3>
            <span class="badge success">Completada</span>
          </header>
          <ul class="appointment-info">
            <li>Detalles del vehículo</li>
            <li>Mecánico asignado</li>
          </ul>
          <div class="appointment-notes">
            <strong>Observaciones:</strong>
            <span>Texto de ejemplo</span>
          </div>
        </article>
      </div>
    </section>

    <!-- Modal -->
    <div class="modal-backdrop" id="modal">
      <div class="modal">
        <div class="m-head">
          <div class="m-title">Agendar Nueva Cita</div>
          <button id="closeModal" class="modal-close">×</button>
        </div>
        <div class="m-body">
          <label>Servicio
            <select name="servicio" id="servicio" required>
            <option value="">Selecciona un servicio</option>
            @foreach($servicios as $servicio)
              <option value="{{ $servicio->id }}">{{ $servicio->nombre }} - ${{ number_format($servicio->precio_base, 2) }}</option>
            @endforeach
            </select>

          </label>
          <label>Información del Vehículo
            <input type="text" placeholder="Ej. Toyota Corolla 2020">
          </label>
          <div class="row">
            <label>Fecha
              <input type="date">
            </label>
            <label>Hora
              <select id="hora">
                <option value="">Selecciona hora</option>
                <option value="08:00">08:00 AM</option>
                <option value="09:00">09:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <option value="11:00">11:00 AM</option>
                <option value="12:00">12:00 PM</option>
                <option value="13:00">1:00 PM</option>
                <option value="14:00">2:00 PM</option>
                <option value="15:00">3:00 PM</option>
                <option value="16:00">4:00 PM</option>
                <option value="17:00">5:00 PM</option>
              </select>
            </label>
          </div>
        </div>
        <div class="m-footer">
          <button id="cancelModal" class="button ghost">Cancelar</button>
          <button id="agendar-btn" class="button primary">Agendar</button>
        </div>
      </div>
    </div>
  </main>

  <script src="{{ asset('frontend/Panel-cliente/cliente.js') }}"></script>
</body>
</html>