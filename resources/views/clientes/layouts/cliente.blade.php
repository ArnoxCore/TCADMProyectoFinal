<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Panel del Cliente | Ctrl + Alt + Del Motors</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- CSS del Cliente -->
    <link rel="stylesheet" href="{{ asset('frontend/Panel-cliente/cliente.css') }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('frontend/icons/favicon-96x96.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('frontend/icons/favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('frontend/icons/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('frontend/icons/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('frontend/icons/site.webmanifest') }}">

    <!-- NOTYF CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
</head>

<body data-page="cliente">

{{-- HEADER GLOBAL DEL CLIENTE --}}
@include('clientes.components.header')

<main class="main">
    @yield('content')
</main>

<!-- NOTYF JS -->
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

<script>
    // Instancia global de Notyf
    window.toast = new Notyf({
        duration: 3500,
        position: {
            x: 'right',
            y: 'top',
        },
        dismissible: true
    });
</script>

{{-- DISPARAR TOASTS DESDE BACKEND --}}
<script>
    @if(session('success'))
    toast.success("{{ session('success') }}");
    @endif

    @if(session('error'))
    toast.error("{{ session('error') }}");
    @endif

    @if(session('warning'))
    toast.warning("{{ session('warning') }}");
    @endif

    @if(session('info'))
    toast.info("{{ session('info') }}");
    @endif
</script>

<!-- CARGA CLIENTE.JS AL FINAL -->
<script src="{{ asset('frontend/Panel-cliente/cliente.js') }}?v={{ time() }}"></script>

</body>
</html>
