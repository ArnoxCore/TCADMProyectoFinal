<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — Servicios</title>
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
<body data-page="servicios">
  <header class="header">
    <div class="wrap">
      <div class="brand">
        <div class="brand-logo" aria-hidden="true"></div>
        <div>
          <div class="brand-title">Panel de Administración</div>
          <div class="brand-sub">Bienvenido, {{ (isset($mecanico) && $mecanico && $mecanico->user) ? $mecanico->user->name : Auth::user()->name }}</div>
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
    <section class="section">
      <div class="section-head">
        <div>
          <h3 style="margin:0;font-size:16px">Servicios Disponibles</h3>
          <p class="section-desc">Gestiona los servicios que ofreces</p>
        </div>
        <button class="button primary" data-open="#modalServicio">+ Nuevo Servicio</button>
      </div>

      @if(session('success'))
        <div data-toast="success" data-message="{{ session('success') }}" style="display:none"></div>
      @endif

      @if($errors->any())
        @foreach($errors->all() as $error)
          <div data-toast="error" data-message="{{ $error }}" style="display:none"></div>
        @endforeach
      @endif

      <div class="table cols-5" style="overflow-x:auto;">
        <header>
          <div>Servicio</div>
          <div>Descripción</div>
          <div>Duración</div>
          <div class="text-right">Precio</div>
          <div class="text-right">Acciones</div>
        </header>

        @forelse($servicios as $servicio)
          <div class="row">
            <div>{{ $servicio->nombre }}</div>
            <div>{{ $servicio->descripcion ?? 'Sin descripción' }}</div>
            <div>{{ $servicio->duracion_estimada ? $servicio->duracion_estimada . ' min' : 'N/D' }}</div>
            <div class="text-right">${{ number_format($servicio->precio_base ?? 0, 2) }}</div>
            <div class="text-right" style="display:flex; justify-content:flex-end; gap:8px;">
              <button type="button"
                      class="button icon-only"
                      aria-label="Editar {{ $servicio->nombre }}"
                      data-open="#modalEditarServicio"
                      data-servicio-id="{{ $servicio->id }}"
                      data-servicio-nombre="{{ e($servicio->nombre) }}"
                      data-servicio-descripcion="{{ e($servicio->descripcion ?? '') }}"
                      data-servicio-duracion="{{ $servicio->duracion_estimada }}"
                      data-servicio-precio="{{ $servicio->precio_base }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z" />
                </svg>
              </button>

              <form method="POST" action="{{ route('admin.servicios.destroy', $servicio) }}" class="form-eliminar-servicio" data-servicio-nombre="{{ e($servicio->nombre) }}" style="margin:0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="button icon-only danger" aria-label="Eliminar {{ $servicio->nombre }}">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V5a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v2m-4 0h12" />
                  </svg>
                </button>
              </form>
            </div>
          </div>
        @empty
          <div class="row" style="grid-column: 1 / -1; text-align:center; color:#777;">
            Aún no hay servicios registrados.
          </div>
        @endforelse
      </div>

      @if($servicios->hasPages())
        <div style="margin-top:16px; display:flex; justify-content:center;">
          {{ $servicios->withQueryString()->links() }}
        </div>
      @endif
    </section>
  </main>

  <!-- Modal Nuevo Servicio -->
  <div class="modal-backdrop" id="modalServicio">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="sTitle">
      <form method="POST" action="{{ route('admin.servicios.store') }}">
        @csrf
        <div class="m-head">
          <div id="sTitle" class="m-title">Nuevo Servicio</div>
          <div class="m-sub">Agrega un servicio a tu catálogo</div>
        </div>
        <div class="m-body">
          <label class="label" for="sNombre">Nombre del servicio</label>
          <input id="sNombre" name="nombre" class="input" placeholder="Ej. Lavado de inyectores" value="{{ old('nombre') }}" required />

          <label class="label" for="sDesc">Descripción</label>
          <textarea id="sDesc" name="descripcion" class="textarea" placeholder="Breve descripción...">{{ old('descripcion') }}</textarea>

          <div class="grid-2">
            <div>
              <label class="label" for="sDuracion">Duración (minutos)</label>
              <input id="sDuracion" name="duracion_estimada" type="number" min="1" class="input" placeholder="Ej. 60" value="{{ old('duracion_estimada') }}" required />
            </div>
            <div>
              <label class="label" for="sPrecio">Precio</label>
              <input id="sPrecio" name="precio_base" type="number" min="0" step="0.01" class="input" placeholder="Ej. 150" value="{{ old('precio_base') }}" required />
            </div>
          </div>
        </div>
        <div class="m-footer">
          <button type="button" class="button" data-close>Cancelar</button>
          <button type="submit" class="button primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Editar Servicio -->
  <div class="modal-backdrop" id="modalEditarServicio">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="editTitle">
      <form method="POST" id="formEditarServicio">
        @csrf
        @method('PUT')
        <div class="m-head">
          <div id="editTitle" class="m-title">Editar Servicio</div>
          <div class="m-sub">Actualiza la información del servicio</div>
        </div>
        <div class="m-body">
          <label class="label" for="editNombre">Nombre del servicio</label>
          <input id="editNombre" name="nombre" class="input" required />

          <label class="label" for="editDesc">Descripción</label>
          <textarea id="editDesc" name="descripcion" class="textarea"></textarea>

          <div class="grid-2">
            <div>
              <label class="label" for="editDuracion">Duración (minutos)</label>
              <input id="editDuracion" name="duracion_estimada" type="number" min="1" class="input" required />
            </div>
            <div>
              <label class="label" for="editPrecio">Precio</label>
              <input id="editPrecio" name="precio_base" type="number" min="0" step="0.01" class="input" required />
            </div>
          </div>
        </div>
        <div class="m-footer">
          <button type="button" class="button" data-close>Cancelar</button>
          <button type="submit" class="button primary">Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
  <script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const editModal = document.querySelector('#modalEditarServicio');
      const editForm = document.getElementById('formEditarServicio');
      const nombreInput = document.getElementById('editNombre');
      const descInput = document.getElementById('editDesc');
      const duracionInput = document.getElementById('editDuracion');
      const precioInput = document.getElementById('editPrecio');
      const editTitle = document.getElementById('editTitle');

      document.querySelectorAll('[data-servicio-id]').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.servicioId;
          const nombre = btn.dataset.servicioNombre || '';
          const descripcion = btn.dataset.servicioDescripcion || '';
          const duracion = btn.dataset.servicioDuracion || '';
          const precio = btn.dataset.servicioPrecio || '';

          nombreInput.value = nombre;
          descInput.value = descripcion;
          duracionInput.value = duracion;
          precioInput.value = precio;
          if (editTitle) {
            editTitle.textContent = nombre ? `Editar Servicio — ${nombre}` : 'Editar Servicio';
          }

          editForm.action = `{{ url('/admin/servicios') }}/${id}`;

          if (editModal) editModal.style.display = 'grid';
        });
      });

      document.querySelectorAll('.form-eliminar-servicio').forEach(form => {
        form.addEventListener('submit', (e) => {
          const nombre = form.dataset.servicioNombre || 'este servicio';
          if (!confirm(`¿Seguro que deseas eliminar ${nombre}?`)) {
            e.preventDefault();
          }
        });
      });
    });
  </script>
  @if($errors->any())
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const modal = document.querySelector('#modalServicio');
        if (modal) modal.style.display = 'grid';
      });
    </script>
  @endif
</body>
</html>