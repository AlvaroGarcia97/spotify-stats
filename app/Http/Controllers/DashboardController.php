<?php

// Carpeta lógica del archivo.
namespace App\Http\Controllers;

use App\Exceptions\SpotifyException; // Nuestro error propio, para "cazarlo" si Spotify falla.
use App\Services\SpotifyService;     // Nuestro "traductor" con la API de Spotify.
use Illuminate\Http\Request;         // Para leer datos que llegan por la URL (ej: ?range=...).

class DashboardController extends Controller
{
    // "SpotifyService $spotify" se inyecta automáticamente: Laravel nos da una instancia ya hecha.
    public function index(Request $request, SpotifyService $spotify)
    {
        // Si no hay token, el usuario no ha entrado: lo mandamos al login.
        if (! session('spotify_access_token')) {
            return redirect()->route('spotify.login');
        }

        // Lista de periodos que Spotify permite, con su etiqueta en español.
        $ranges = [
            'short_term'  => '4 semanas',
            'medium_term' => '6 meses',
            'long_term'   => 'Varios años',
        ];

        // Leemos el periodo elegido desde la URL (?range=...).
        // Si no viene, o no es válido, usamos "medium_term" por defecto.
        $range = $request->query('range', 'medium_term');
        if (! array_key_exists($range, $ranges)) {
            $range = 'medium_term';
        }

        // Preparamos valores por defecto por si algo falla.
        $artists = [];
        $tracks  = [];
        $error   = null;

        // "try" intenta hacer el trabajo; si algo lanza una excepción, salta al "catch".
        try {
            // Pedimos tus artistas más escuchados. El servicio pone el token y,
            // si está caducado, lo refresca y reintenta solo.
            $artists = $spotify->get('/me/top/artists', [
                'time_range' => $range,
                'limit'      => 12,
            ])['items'] ?? [];

            // Y también tus CANCIONES más escuchadas.
            $tracks = $spotify->get('/me/top/tracks', [
                'time_range' => $range,
                'limit'      => 10,
            ])['items'] ?? [];
        } catch (SpotifyException $e) {
            // Guardamos el mensaje para enseñarlo en la vista.
            $error = $e->getMessage();
        }

        // Enviamos las listas, los periodos, el elegido y el posible error a la vista.
        return view('dashboard', compact('artists', 'tracks', 'ranges', 'range', 'error'));
    }
}
