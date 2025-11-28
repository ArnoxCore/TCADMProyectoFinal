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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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

<!-- JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-toast]').forEach(node => {
            const type = (node.dataset.toast || 'success').toLowerCase();
            const message = (node.dataset.message || node.textContent || '').trim();
            if (!message) {
                node.remove();
                return;
            }

            switch (type) {
                case 'error':
                    toast.error(message);
                    break;
                case 'warning':
                    toast.warning(message);
                    break;
                case 'info':
                    toast.info ? toast.info(message) : toast.open({ message, duration: 3500 });
                    break;
                default:
                    toast.success(message);
            }

            node.remove();
        });
    });
</script>

<!-- CARGA CLIENTE.JS AL FINAL -->
<script src="{{ asset('frontend/Panel-cliente/cliente.js') }}?v={{ time() }}"></script>

</body>
</html>
