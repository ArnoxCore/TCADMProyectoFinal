<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin — Mis datos</title>
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
<body data-page="perfil">
  <header class="header">
    <div class="wrap">
      <div class="brand">
        <div class="brand-logo" aria-hidden="true"></div>
        <div>
          <div class="brand-title">Panel de Administración</div>
          <div class="brand-sub">Bienvenido, {{ $user->name }}</div>
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

  <main class="main" style="max-width: 960px;">
    @if (session('status') === 'admin-profile-updated')
      <div data-toast="success" data-message="Tus datos se actualizaron correctamente." style="display:none"></div>
    @endif

    @if ($errors->any() || session('errors'))
      @php($bag = $errors->getBag('default'))
      @foreach ($bag->all() as $error)
        <div data-toast="error" data-message="{{ $error }}" style="display:none"></div>
      @endforeach
    @endif

    <section class="section">
      <div class="card">
        <div class="head">
          <div>
            <div class="title">Datos de la cuenta</div>
            <p class="section-desc">Actualiza tu nombre y correo. Guardar cambios cerrará tu sesión.</p>
          </div>
        </div>
        <div class="body">
          <form method="POST" action="{{ route('admin.perfil.update') }}" class="section" style="gap:16px;">
            @csrf
            @method('PUT')
            <div>
              <label class="label" for="perfilNombre">Nombre completo</label>
              <input id="perfilNombre" class="input" type="text" name="name" value="{{ old('name', $user->name) }}" required>
              @error('name')
                <p style="color:#b91c1c;font-size:13px;margin-top:4px;">{{ $message }}</p>
              @enderror
            </div>
            <div>
              <label class="label" for="perfilCorreo">Correo electrónico</label>
              <input id="perfilCorreo" class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
              @error('email')
                <p style="color:#b91c1c;font-size:13px;margin-top:4px;">{{ $message }}</p>
              @enderror
            </div>
            <div style="display:flex;justify-content:flex-end;">
              <button type="submit" class="button primary">Guardar cambios</button>
            </div>
          </form>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="card">
        <div class="head">
          <div>
            <div class="title">Cambiar contraseña</div>
            <p class="section-desc">Al guardar se cerrará tu sesión para aplicar la nueva contraseña.</p>
          </div>
        </div>
        <div class="body">
          @if ($errors->hasBag('updatePassword'))
            @foreach ($errors->updatePassword->all() as $error)
              <div data-toast="error" data-message="{{ $error }}" style="display:none"></div>
            @endforeach
          @endif

          <form method="POST" action="{{ route('admin.perfil.password') }}" class="section" style="gap:16px;">
            @csrf
            @method('PUT')
            <div>
              <label class="label" for="passwordActual">Contraseña actual</label>
              <input id="passwordActual" class="input" type="password" name="current_password" autocomplete="current-password" required>
              @foreach ($errors->updatePassword->get('current_password', []) as $message)
                <p style="color:#b91c1c;font-size:13px;margin-top:4px;">{{ $message }}</p>
              @endforeach
            </div>
            <div class="grid-2">
              <div>
                <label class="label" for="passwordNueva">Nueva contraseña</label>
                <input id="passwordNueva" class="input" type="password" name="password" autocomplete="new-password" required>
                @foreach ($errors->updatePassword->get('password', []) as $message)
                  <p style="color:#b91c1c;font-size:13px;margin-top:4px;">{{ $message }}</p>
                @endforeach
              </div>
              <div>
                <label class="label" for="passwordConfirmacion">Confirmar contraseña</label>
                <input id="passwordConfirmacion" class="input" type="password" name="password_confirmation" autocomplete="new-password" required>
                @foreach ($errors->updatePassword->get('password_confirmation', []) as $message)
                  <p style="color:#b91c1c;font-size:13px;margin-top:4px;">{{ $message }}</p>
                @endforeach
              </div>
            </div>
            <div style="display:flex;justify-content:flex-end;">
              <button type="submit" class="button primary">Actualizar contraseña</button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
  <script src="{{ asset('frontend/Panel-admin/admin.js') }}"></script>
</body>
</html>
