<?php

// Un "controlador" es la clase que contiene la lógica de cada pantalla.
// Aquí traemos el que se encargará de todo el tema del login con Spotify.
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaylistGeneratorController;
use App\Http\Controllers\SpotifyAuthController;

// "Route" es la herramienta de Laravel que conecta una URL con el código que debe ejecutarse.
use Illuminate\Support\Facades\Route;

// Página principal: muestra la portada con el botón de iniciar sesión.
Route::get('/', function () {
    return view('home');
})->name('home');

// Cerrar sesión: olvidamos el token (y el state) y volvemos a la portada.
// "forget" borra esos datos de la sesión del usuario.
Route::get('/logout', function () {
    session()->forget(['spotify_access_token', 'spotify_refresh_token', 'spotify_state']);

    return redirect()->route('home');
})->name('logout');

// RUTA 1 (el "timbre" de la puerta): paso 1 del login.
// Cuando el usuario entra en /login, se ejecuta el método "redirect" del controlador.
// Su trabajo será mandar al usuario a la pantalla de Spotify donde le pide permiso.
Route::get('/login', [SpotifyAuthController::class, 'redirect'])
    ->name('spotify.login'); // El "name" es un apodo para llamar a esta ruta desde otros sitios.

// RUTA 2: paso 3 del login (la vuelta).
// Spotify devolverá al usuario aquí después de que acepte los permisos.
// OJO: esta dirección debe coincidir EXACTAMENTE con el "Redirect URI" que pusiste
// en el panel de desarrollador de Spotify (http://127.0.0.1:8000/callback).
Route::get('/callback', [SpotifyAuthController::class, 'callback'])
    ->name('spotify.callback');

// RUTA 3: el panel de estadísticas.
// Cuando el usuario entra en /dashboard, se ejecuta "index" del DashboardController.
// Ahí comprobaremos que está logueado (que tiene token) y le mostraremos sus datos.
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Generador de playlists por estado de ánimo.
// GET: solo muestra la página con los botones.
Route::get('/generator', [PlaylistGeneratorController::class, 'index'])
    ->name('generator.index');

// POST: crea la playlist (acción que cambia datos, por eso usamos POST).
Route::post('/generator', [PlaylistGeneratorController::class, 'store'])
    ->name('generator.store');
