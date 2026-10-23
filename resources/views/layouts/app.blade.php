<!DOCTYPE html>
{{-- ESTE ES EL ESQUELETO COMÚN DE TODAS LAS PÁGINAS --}}
{{-- Las demás vistas (home, dashboard) lo reutilizan con @extends y rellenan @yield --}}
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- @yield('title') deja un hueco: cada página pone su propio título. --}}
    <title>@yield('title', 'Spotify Stats')</title>

    {{-- Tailwind cargado desde internet (CDN): da los estilos sin instalar nada. --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-neutral-950 text-neutral-100 antialiased">

    {{-- CABECERA: se repite en todas las páginas --}}
    <header class="border-b border-white/10">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold">
                <span class="text-2xl text-[#1DB954]">●</span>
                <span>Spotify Stats</span>
            </a>

        @if (session('spotify_access_token'))
            {{-- Si hay token, el usuario está logueado: mostramos accesos y logout. --}}
            <div class="flex items-center gap-5">
                <a href="{{ route('dashboard') }}" class="text-sm text-neutral-300 hover:text-white">Estadísticas</a>
                <a href="{{ route('generator.index') }}" class="text-sm text-neutral-300 hover:text-white">Generador</a>
                <a href="{{ route('logout') }}" class="text-sm text-neutral-400 hover:text-white">Cerrar sesión</a>
            </div>
            @else
                {{-- Si no hay token, mostramos el botón para entrar. --}}
                <a href="{{ route('spotify.login') }}" class="rounded-full bg-[#1DB954] px-4 py-2 text-sm font-semibold text-black hover:bg-[#1ed760]">
                    Iniciar sesión
                </a>
            @endif
        </div>
    </header>

    {{-- CONTENIDO: aquí va lo propio de cada página. --}}
    <main class="mx-auto max-w-6xl px-6 py-10">
        @yield('content')
    </main>

</body>
</html>
