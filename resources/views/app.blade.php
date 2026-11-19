<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'VN Maker') }}</title>

        {{-- Aplica el tema guardado antes de pintar para evitar el destello púrpura. --}}
        <script>
            (() => {
                const temas = ['current', 'gothic', 'hopecore', 'dark', 'white', 'hellfire'];

                try {
                    const guardado = localStorage.getItem('vn-maker-theme');
                    document.documentElement.dataset.vnTheme = temas.includes(guardado)
                        ? guardado
                        : 'current';
                } catch {
                    document.documentElement.dataset.vnTheme = 'current';
                }
            })();
        </script>

        {{-- Fuentes únicas para toda la aplicación. --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Rubik:wght@400;500;600&display=swap"
            rel="stylesheet"
        >

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
