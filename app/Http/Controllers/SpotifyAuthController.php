<?php

// Indicamos en qué "carpeta lógica" vive este archivo (Laravel lo usa para encontrarlo).
namespace App\Http\Controllers;

use Illuminate\Http\Request;              // Herramienta para leer lo que llega por la URL.
use Illuminate\Support\Facades\Http;      // Herramienta para hacer peticiones HTTP (hablar con Spotify).
use Illuminate\Support\Str;               // Herramienta para generar textos aleatorios.

class SpotifyAuthController extends Controller
{
    // PASO 1: mandar al usuario a la pantalla de permiso de Spotify.
    public function redirect()
    {
        // 1) Creamos un texto aleatorio llamado "state".
        //    Sirve como comprobante de seguridad (ver más abajo).
        $state = Str::random(40);

        // 2) Guardamos ese "state" en la SESIÓN del usuario.
        //    La sesión es la memoria del servidor para ese visitante concreto.
        session(['spotify_state' => $state]);

        // 3) Montamos la dirección a la que enviaremos al usuario,
        //    poniendo los datos como si fueran un formulario en la URL (query string).
        $query = http_build_query([
            'client_id'     => config('services.spotify.client_id'),     // Quién es nuestra app.
            'response_type' => 'code',                                   // Le pedimos un "code".
            'redirect_uri'  => config('services.spotify.redirect_uri'),  // A dónde debe volver.
            // Permisos (scopes): leer tus tops + crear/editar playlists.
            // Si añades permisos nuevos, hay que volver a iniciar sesión.
            'scope' => 'user-top-read playlist-modify-public playlist-modify-private',
            'state'         => $state,                                   // Le mandamos nuestro comprobante.
        ]);

        // 4) Redirigimos el navegador a Spotify con todo eso.
        return redirect('https://accounts.spotify.com/authorize?'.$query);
    }

    // PASO 3 y 4: Spotify nos devuelve aquí con el "code".
    public function callback(Request $request)
    {
        // PASO 3: comprobamos que el "state" que devuelve Spotify es el mismo
        // que guardamos antes. Si no coincide, cortamos por seguridad (error 403).
        abort_unless($request->state === session('spotify_state'), 403);

        // PASO 4: cambiamos el "code" por un "token".
        // Esta petición la hace NUESTRO SERVIDOR (el navegador no ve el secreto).
        $response = Http::asForm() // Enviamos los datos como un formulario.
            ->withBasicAuth(       // Spotify exige mandar ID y secreto como usuario/contraseña.
                config('services.spotify.client_id'),
                config('services.spotify.client_secret')
            )
            ->post('https://accounts.spotify.com/api/token', [ // La dirección del "mostrador" de tokens.
                'grant_type'   => 'authorization_code',        // "Vengo a cambiar un code".
                'code'         => $request->code,              // El code que nos dio Spotify.
                'redirect_uri' => config('services.spotify.redirect_uri'),
            ]);

        // Convertimos la respuesta de Spotify en un array de PHP para poder usarla.
        $data = $response->json();

        // Guardamos el token en la sesión para usarlo en las siguientes pantallas.
        // También guardamos el "refresh token", que sirve para pedir otro token luego.
        session([
            'spotify_access_token'  => $data['access_token'] ?? null,
            'spotify_refresh_token' => $data['refresh_token'] ?? null,
        ]);

        // Mandamos al usuario a su panel de estadísticas (lo crearemos en el siguiente paso).
        return redirect()->route('dashboard');
    }
}
