@extends('layouts.app')

@section('title', 'Generador de playlists · Spotify Stats')

@section('content')
    <h1 class="text-3xl font-bold">Crea una playlist con tu música</h1>
    <p class="mt-2 text-neutral-400">Elige de dónde sacar las canciones y el periodo. Crearemos la playlist en tu cuenta.</p>

    {{-- Si algo falló al generar (error de Spotify, sin canciones...), lo mostramos en rojo. --}}
    @if (session('error'))
        <div class="mt-6 rounded-2xl border border-red-500/40 bg-red-500/10 p-5 text-red-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- Si acabamos de crear una playlist, mostramos un aviso de éxito.
         'playlist' solo existe un momento (mensaje "flash" que se borra al siguiente recargo). --}}
    @if (session('playlist'))
        <div class="mt-6 rounded-2xl border border-[#1DB954]/40 bg-[#1DB954]/10 p-5">
            <p class="font-semibold text-[#1DB954]">¡Playlist creada!</p>
            <a href="{{ session('playlist')['external_urls']['spotify'] }}" target="_blank" rel="noopener"
               class="mt-1 inline-block text-sm text-neutral-200 hover:underline">
                {{ session('playlist')['name'] }} →
            </a>
        </div>
    @endif

    {{-- Dos MODOS de generar: con tus artistas top o con tus canciones top.
         En cada modo, 3 botones de periodo. Todos usan POST (crean algo). --}}
    @foreach (['artists' => 'Con tus artistas top', 'tracks' => 'Con tus canciones top'] as $mode => $titulo)
        <section class="mt-10">
            <h2 class="mb-4 text-xl font-semibold">{{ $titulo }}</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ($ranges as $key => $label)
                    <form method="POST" action="{{ route('generator.store') }}">
                        {{-- @csrf es un candado de seguridad que Laravel exige en los formularios. --}}
                        @csrf
                        {{-- Estos datos viajan "escondidos": el usuario no los ve, pero se envían. --}}
                        <input type="hidden" name="mode" value="{{ $mode }}">
                        <input type="hidden" name="range" value="{{ $key }}">
                        <button type="submit"
                                class="w-full rounded-2xl border border-white/10 bg-white/5 p-6 font-semibold transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-50">
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    @endforeach

    {{-- HISTORIAL: las últimas playlists que hemos generado, guardadas en la base de datos. --}}
    @if ($history->isNotEmpty())
        <section class="mt-14">
            <h2 class="mb-4 text-xl font-semibold">Historial</h2>

            <ul class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                @foreach ($history as $item)
                    <li class="flex items-center justify-between gap-4 border-b border-white/5 p-4 last:border-b-0 hover:bg-white/5">
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ $item->name }}</p>
                            {{-- diffForHumans() convierte la fecha en texto tipo "hace 3 minutos". --}}
                            <p class="text-sm text-neutral-400">
                                {{ $item->tracks_count }} canciones · {{ $item->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <a href="{{ $item->url }}" target="_blank" rel="noopener"
                           class="text-xs font-semibold text-[#1DB954] hover:underline">
                            Abrir
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Pequeño JS: al enviar un formulario, desactivamos su botón y cambiamos el texto,
         para evitar que el usuario pulse dos veces y se creen dos playlists. --}}
    <script>
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function () {
                const boton = form.querySelector('button');
                boton.disabled = true;
                boton.textContent = 'Generando...';
            });
        });
    </script>
@endsection
