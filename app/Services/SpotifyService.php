<?php

// "Services" es una carpeta donde guardamos clases con lógica reutilizable.
// Esta se encarga de TODO lo relacionado con hablar con la API de Spotify.
namespace App\Services;

use App\Exceptions\SpotifyException;    // Nuestro error propio, para avisar si Spotify falla.
use Illuminate\Support\Facades\Http;

class SpotifyService
{
    // Dirección base de la API. Cada método le añade la parte final (ej: /me/top/artists).
    protected string $baseUrl = 'https://api.spotify.com/v1';

    // Petición GET (pedir datos).
    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    // Petición POST (crear cosas).
    public function post(string $path, array $body = []): array
    {
        return $this->request('post', $path, $body);
    }

    // Aquí está la "magia": un único sitio para pedir a Spotify,
    // con reintento automático si el token ha caducado.
    protected function request(string $method, string $path, array $data = []): array
    {
        $response = Http::withToken(session('spotify_access_token'))
            ->{$method}($this->baseUrl.$path, $data); // $method es "get" o "post".

        // 401 = "tu token ya no vale". Lo refrescamos y reintentamos UNA vez.
        if ($response->status() === 401) {
            $this->refreshToken();

            $response = Http::withToken(session('spotify_access_token'))
                ->{$method}($this->baseUrl.$path, $data);
        }

        // Si Spotify respondió con otro error (400, 403, 429...), lanzamos nuestra excepción
        // con el mensaje que nos dio Spotify, para que el controlador lo enseñe.
        if (! $response->successful()) {
            $mensaje = $response->json('error.message') ?? 'Error desconocido de Spotify';

            throw new SpotifyException($mensaje, $response->status());
        }

        // Devolvemos el JSON como array de PHP (o [] si no vino nada).
        return $response->json() ?? [];
    }

    // Pide un token nuevo usando el "refresh token" y lo guarda en la sesión.
    protected function refreshToken(): void
    {
        $response = Http::asForm()
            ->withBasicAuth(
                config('services.spotify.client_id'),      // Identifica a nuestra app.
                config('services.spotify.client_secret')   // Y su secreto (nunca sale al navegador).
            )
            ->post('https://accounts.spotify.com/api/token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => session('spotify_refresh_token'),
            ]);

        // Si el refresco también falla (ej: refresh_token caducado), avisamos.
        if (! $response->successful()) {
            throw new SpotifyException('Tu sesión de Spotify ha caducado, vuelve a iniciar sesión.', 401);
        }

        $data = $response->json();

        // Guardamos el token de acceso nuevo.
        session(['spotify_access_token' => $data['access_token'] ?? null]);

        // A veces Spotify devuelve TAMBIÉN un refresh_token nuevo: si viene, lo actualizamos.
        if (! empty($data['refresh_token'])) {
            session(['spotify_refresh_token' => $data['refresh_token']]);
        }
    }
}
