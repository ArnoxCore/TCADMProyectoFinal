<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ctrl Alt Del Motors</title>

    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- ====== FAVICON / PWA ====== -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('frontend/icons/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('frontend/icons/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('frontend/icons/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/icons/apple-touch-icon.png') }}">
    <meta name="apple-mobile-web-app-title" content="TCADM">
    <link rel="manifest" href="{{ asset('frontend/icons/site.webmanifest') }}">

    {{-- Ruta al CSS público --}}
    <link rel="stylesheet" href="{{ asset('frontend/Principal/styles.css') }}">
</head>
<body>
<header class="site-header" role="banner">
    <div class="container">
        <a class="brand" href="/" aria-label="Inicio">
            <span class="brand__main">Ctrl Alt Del</span>
            <span class="brand__accent">Motors</span>
        </a>
    </div>
</header>

<main>
    <section class="hero" aria-label="Taller automotriz">
        <div class="hero__overlay"></div>
        <div class="container hero__content">
            <h1 class="hero__title">
                Ctrl Alt Del <span class="brand__accent">Motors</span>
            </h1>
            <p class="hero__subtitle">Regístrate y agenda tu cita ahora</p>

            <a class="btn btn--primary" href="{{ route('redirigir') }}">
                Agendar Cita
                <svg class="btn__icon" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 12h14M13 5l7 7-7 7"
                          fill="none" stroke="currentColor" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>

        </div>
    </section>
</main>
</body>
</html>
