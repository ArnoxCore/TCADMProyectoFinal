<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro | Ctrl Alt Del Motors</title>

    <!-- ===== Fuentes ===== -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- ===== Estilos ===== -->
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
            <img src="{{ asset('frontend/icons/logo.png') }}" alt="Logo Ctrl Alt Del Motors">
            <h1>Ctrl + Alt + Del Motors</h1>
            <p>Sistema de Gestión de Citas</p>
        </header>

        <div class="auth-tabs">
            <button class="auth-tab" onclick="window.location.href='{{ route('login') }}'">Iniciar Sesión</button>
            <button class="auth-tab active">Registrarse</button>
        </div>

        <div class="auth-card">
            <h2>Crear Cuenta</h2>
            <p>Regístrate como nuevo cliente</p>

            <!-- ==== FORMULARIO FUNCIONAL ==== -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Mostrar errores -->
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

                <div class="form-group">
                    <label for="name">Nombre completo</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Juan Pérez" required autofocus>
                </div>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="tu@correo.com" required>
                </div>

                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Ej. 3221234567">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
                </div>

                <button type="submit" class="auth-button">Registrar Cuenta</button>
            </form>
        </div>

        <footer class="auth-footer">
            © {{ date('Y') }} Ctrl + Alt + Del Motors. Todos los derechos reservados.
        </footer>
    </div>
</div>
</body>
</html>
