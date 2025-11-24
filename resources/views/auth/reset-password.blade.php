<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer contraseña | Ctrl Alt Del Motors</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontend/Login-Register/base.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/Login-Register/auth.css') }}">

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
            <button class="auth-tab" onclick="window.location.href='{{ route('login') }}'">Iniciar Sesión</button>
            <button class="auth-tab active">Restablecer contraseña</button>
        </div>

        <div class="auth-card">
            <h2>Restablece tu contraseña</h2>
            <p>Define una nueva contraseña para tu cuenta.</p>

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

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}" placeholder="tu@correo.com" required autofocus autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Nueva contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••" required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••" required autocomplete="new-password">
                </div>

                <button class="auth-button" type="submit">Guardar nueva contraseña</button>

                <div class="auth-extra" style="margin-top:1rem;text-align:center;">
                    <a href="{{ route('login') }}">Volver al inicio de sesión</a>
                </div>
            </form>
        </div>

        <footer class="auth-footer">
            © {{ date('Y') }} Ctrl + Alt + Del Motors. Todos los derechos reservados.
        </footer>
    </div>
</div>
</body>
</html>
