@extends('layouts.app')

@section('title', 'Tu panel · Spotify Stats')

@section('content')
    {{-- Si hubo un error al hablar con Spotify, lo mostramos en rojo. --}}
    @if ($error)
        <div class="mb-8 rounded-2xl border border-red-500/40 bg-red-500/10 p-5 text-red-300">
            {{ $error }}
        </div>
    @endif

    {{-- flexbox + justify-between: el título a la izquierda, los botones a la derecha. --}}
    <header class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold">Tu música más escuchada</h1>
            <p class="mt-2 text-neutral-400">Basado en: {{ $ranges[$range] }}.</p>
        </div>

        {{-- Cada botón es un ENLACE a la misma página cambiando ?range=...
             route('dashboard', ['range' => $key]) genera por ejemplo /dashboard?range=long_term --}}
        <nav class="flex gap-2">
            @foreach ($ranges as $key => $label)
                <a href="{{ route('dashboard', ['range' => $key]) }}"
                   class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $range === $key ? 'bg-[#1DB954] text-black' : 'border border-white/15 text-neutral-300 hover:bg-white/5' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </header>

    {{-- ===================== ARTISTAS ===================== --}}
    <h2 class="mb-5 text-xl font-semibold">Artistas</h2>

    @if (empty($artists))
        <p class="rounded-2xl border border-white/10 bg-white/5 p-8 text-center text-neutral-400">
            No hemos podido cargar tus artistas. Prueba a cerrar sesión y volver a entrar.
        </p>
    @else
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
            {{-- @foreach repite el bloque por cada artista de la lista. --}}
            @foreach ($artists as $artist)
                <article class="rounded-2xl border border-white/10 bg-white/5 p-4 text-center transition hover:bg-white/10">
                    {{-- Foto del artista (la primera imagen que devuelve Spotify). --}}
                    @if (! empty($artist['images'][0]['url']))
                        <img src="{{ $artist['images'][0]['url'] }}"
                             alt="{{ $artist['name'] }}"
                             class="mx-auto h-28 w-28 rounded-full object-cover">
                    @endif

                    {{-- Nombre del artista. {{ }} escapa el texto para que sea seguro. --}}
                    <h3 class="mt-4 font-semibold">{{ $artist['name'] }}</h3>

                    {{-- Enlace al artista en Spotify (el dato 'external_urls' SÍ existe). --}}
                    @if (! empty($artist['external_urls']['spotify']))
                        <a href="{{ $artist['external_urls']['spotify'] }}" target="_blank" rel="noopener"
                           class="mt-3 inline-block text-xs font-semibold text-[#1DB954] hover:underline">
                            Ver en Spotify →
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    {{-- ===================== CANCIONES ===================== --}}
    <h2 class="mt-14 mb-5 text-xl font-semibold">Canciones</h2>

    @if (empty($tracks))
        <p class="rounded-2xl border border-white/10 bg-white/5 p-8 text-center text-neutral-400">
            No hemos podido cargar tus canciones.
        </p>
    @else
        <ol class="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
            @foreach ($tracks as $index => $track)
                <li class="flex items-center gap-4 border-b border-white/5 p-3 last:border-b-0 hover:bg-white/5">
                    {{-- Número de posición (index empieza en 0, por eso +1). --}}
                    <span class="w-6 text-center text-sm text-neutral-500">{{ $index + 1 }}</span>

                    {{-- Portada del álbum. --}}
                    @if (! empty($track['album']['images'][0]['url']))
                        <img src="{{ $track['album']['images'][0]['url'] }}"
                             alt="{{ $track['name'] }}"
                             class="h-12 w-12 rounded object-cover">
                    @endif

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium">{{ $track['name'] }}</p>
                        {{-- join une los nombres de los artistas con comas. --}}
                        <p class="truncate text-sm text-neutral-400">
                            {{ collect($track['artists'])->pluck('name')->join(', ') }}
                        </p>
                    </div>

                    {{-- Duración en minutos:segundos, calculada a partir de milisegundos. --}}
                    <span class="text-sm text-neutral-500">
                        {{ floor($track['duration_ms'] / 60000) }}:{{ str_pad(floor(($track['duration_ms'] % 60000) / 1000), 2, '0', STR_PAD_LEFT) }}
                    </span>

                    @if (! empty($track['external_urls']['spotify']))
                        <a href="{{ $track['external_urls']['spotify'] }}" target="_blank" rel="noopener"
                           class="text-xs font-semibold text-[#1DB954] hover:underline">
                            Abrir
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
@endsection
