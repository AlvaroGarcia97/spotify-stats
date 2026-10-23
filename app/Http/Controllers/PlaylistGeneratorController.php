<?php

// Carpeta lógica donde vive este archivo (Laravel lo usa para encontrarlo).
namespace App\Http\Controllers;

use App\Exceptions\SpotifyException; // Nuestro error propio, para "cazarlo" si Spotify falla.
use App\Models\GeneratedPlaylist;    // El modelo de la tabla donde guardamos el historial.
use App\Services\SpotifyService;     // Nuestro "traductor" con la API de Spotify.
use Illuminate\Http\Request;         // Para leer los datos que envía el formulario (POST).

class PlaylistGeneratorController extends Controller
{
    // GET /generator -> solo MUESTRA la página con los botones de periodo.
    public function index()
    {
        // Si no hay token, el usuario no está logueado: lo mandamos a iniciar sesión.
        if (! session('spotify_access_token')) {
            return redirect()->route('spotify.login');
        }

        // Le pasamos a la vista los periodos (para los botones) y el historial
        // (las últimas 10 playlists generadas, las más nuevas primero).
        return view('generator', [
            'ranges'  => $this->ranges(),
            'history' => GeneratedPlaylist::latest()->take(10)->get(),
        ]);
    }

    // POST /generator -> crea la playlist según el "modo" y el periodo elegidos.
    public function store(Request $request, SpotifyService $spotify)
    {
        // Si no hay token, el usuario no está logueado.
        if (! session('spotify_access_token')) {
            return redirect()->route('spotify.login');
        }

        // Leemos lo que eligió el usuario en el <form>.
        $mode   = $request->input('mode');   // 'artists' o 'tracks'.
        $range  = $request->input('range');  // 'short_term', 'medium_term' o 'long_term'.
        $ranges = $this->ranges();

        // Si algún valor no es válido, cortamos con error 404.
        abort_unless(isset($ranges[$range]), 404);
        abort_unless(in_array($mode, ['artists', 'tracks'], true), 404);

        // "try" intenta hacer el trabajo; si algo lanza una excepción, salta al "catch".
        try {
            // Según el modo, sacamos los "uri" de las canciones de una forma u otra.
            $uris = $mode === 'artists'
                ? $this->urisFromTopArtists($spotify, $range)
                : $this->urisFromTopTracks($spotify, $range);

            // Si no encontramos ninguna canción, avisamos y no creamos nada.
            if (empty($uris)) {
                return redirect()->route('generator.index')
                    ->with('error', 'No encontramos canciones para generar la playlist.');
            }

            // Texto para el nombre de la playlist.
            $titulo = $mode === 'artists' ? 'artistas top' : 'canciones top';

            // --- Crear una playlist vacía en la cuenta del usuario ---
            $playlist = $spotify->post('/me/playlists', [
                'name'        => 'Tus '.$titulo.' · '.$ranges[$range],
                'description' => 'Generada por Spotify Stats con tus datos más escuchados',
                'public'      => false, // Privada (solo para ti).
            ]);

            // --- Meter las canciones encontradas dentro de la playlist ---
            $spotify->post('/playlists/'.$playlist['id'].'/items', [
                'uris' => $uris,
            ]);

            // --- Guardar esta playlist en el historial (una fila en la tabla) ---
            GeneratedPlaylist::create([
                'spotify_playlist_id' => $playlist['id'],
                'name'                => $playlist['name'],
                'mode'                => $mode,
                'range'               => $ranges[$range],
                'tracks_count'        => count($uris),
                'url'                 => $playlist['external_urls']['spotify'] ?? '',
            ]);
        } catch (SpotifyException $e) {
            // Si algo fallaba, volvemos con un aviso de error en vez de romper.
            return redirect()->route('generator.index')
                ->with('error', 'Spotify dijo: '.$e->getMessage());
        }

        // Todo fue bien: volvemos llevando la playlist creada para mostrar un aviso.
        return redirect()->route('generator.index')->with('playlist', $playlist);
    }

    // Opción 1: recoge canciones buscándolas a partir de TUS artistas más escuchados.
    protected function urisFromTopArtists(SpotifyService $spotify, string $range): array
    {
        // Pedimos tus artistas más escuchados en ese periodo.
        // OJO: esta respuesta trae la lista directamente en 'items' (no anidada).
        $topArtists = $spotify->get('/me/top/artists', [
            'time_range' => $range,
            'limit'      => 5, // Cogemos los 5 primeros artistas.
        ])['items'] ?? [];

        $uris = [];

        foreach ($topArtists as $artist) {
            $tracks = $spotify->get('/search', [
                'q'     => 'artist:"'.$artist['name'].'"', // Canciones DE este artista.
                'type'  => 'track',
                'limit' => 5,                              // Máximo 10 permite Spotify.
            ])['tracks']['items'] ?? [];

            // Nos quedamos con 3 canciones de cada artista para que la lista esté mezclada.
            foreach (array_slice($tracks, 0, 3) as $track) {
                $uris[] = $track['uri'];
            }
        }

        // Quitamos repetidos y reindexamos el array.
        return array_values(array_unique($uris));
    }

    // Opción 2: recoge directamente TUS canciones más escuchadas (sin buscar).
    protected function urisFromTopTracks(SpotifyService $spotify, string $range): array
    {
        $tracks = $spotify->get('/me/top/tracks', [
            'time_range' => $range,
            'limit'      => 20, // Tus 20 canciones más escuchadas del periodo.
        ])['items'] ?? [];

        // De cada canción solo necesitamos su "uri".
        return collect($tracks)->pluck('uri')->all();
    }

    // Periodos disponibles: la clave es lo que entiende Spotify, el valor lo que ve el usuario.
    protected function ranges(): array
    {
        return [
            'short_term'  => '4 semanas',
            'medium_term' => '6 meses',
            'long_term'   => 'Varios años',
        ];
    }
}
