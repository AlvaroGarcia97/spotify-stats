{{-- @extends hereda el esqueleto común (cabecera y estilos). --}}
@extends('layouts.app')

@section('title', 'Inicio · Spotify Stats')

@section('content')
    {{-- HERO: la zona grande de bienvenida. --}}
    <section class="py-20 text-center">
        <h1 class="mx-auto max-w-3xl text-4xl font-bold sm:text-6xl">
            Descubre tus <span class="text-[#1DB954]">artistas más escuchados</span> de Spotify
        </h1>

        <p class="mx-auto mt-6 max-w-xl text-neutral-400">
            Inicia sesión con tu cuenta de Spotify y mira un resumen de lo que más suena en tu perfil.
        </p>

        {{-- Botón que lleva a la ruta /login (el paso 1 del login). --}}
        <a href="{{ route('spotify.login') }}"
           class="mt-10 inline-block rounded-full bg-[#1DB954] px-8 py-4 text-lg font-semibold text-black transition hover:bg-[#1ed760]">
            Iniciar sesión con Spotify
        </a>
    </section>
@endsection
