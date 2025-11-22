<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión | Ctrl Alt Del Motors</title>

    <!-- Fuentes y estilos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/Login-Register/base.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/Login-Register/auth.css') }}">

    <!-- ====== FAVICON / PWA ====== -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('frontend/icons/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('frontend/icons/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('frontend/icons/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/icons/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-title" content="TCADM">
    <link rel="manifest" href="{{ asset('frontend/icons/site.webmanifest') }}">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-container">
        <header class="auth-header">
            <img src="{{ asset('frontend/icons/favicon-96x96.png') }}" alt="Logo Ctrl Alt Del Motors">
            <h1>Ctrl + Alt + Del Motors</h1>
            <p>Sistema de Gestión de Citas</p>
        </header>

        <div class="auth-tabs">
            <button class="auth-tab active">Iniciar Sesión</button>
            <button class="auth-tab" onclick="window.location.href='{{ route('register') }}'">Registrarse</button>
        </div>

        <div class="auth-card">
            <h2>Iniciar Sesión</h2>
            <p>Ingresa tus credenciales para acceder al sistema</p>

            <!-- Mensaje de éxito tras registrarse -->
            @if (session('success'))
                <div style="background:#e6ffed;color:#056c3b;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Mostrar errores de validación -->
            @if ($errors->any())
                <div style="background:#ffe5e5;color:#b00020;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;">
                    <strong>⚠ Errores de validación:</strong>
                    <ul style="margin:0.5rem 0 0 1rem;font-size:0.9rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="tu@correo.com" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Ingresa tu Contraseña" required>
                </div>

                {{-- <div class="auth-extra">
                    <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                </div> --}}

                <button class="auth-button" type="submit">Iniciar Sesión</button>
            </form>
        </div>

        <footer class="auth-footer">
            © {{ date('Y') }} Ctrl + Alt + Del Motors. Todos los derechos reservados.
        </footer>
    </div>
</div>
</body>
</html>
