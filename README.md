# Spotify Stats

Aplicacin web (Laravel) que se conecta a tu cuenta de Spotify para mostrar tus
estadsticas y **generar playlists** a partir de tus artistas y canciones ms escuchadas.

Es un proyecto de aprendizaje: hace login con Spotify (OAuth 2.0), consume su API y
guarda un historial en base de datos.

> **Nota:** Spotify limita las apps en modo desarrollo a usuarios autorizados. Para verlo
> sin login restringido necesitaras solicitar *Extended Quota* a Spotify.

## Funcionalidades

- **Login con Spotify** (OAuth 2.0 Authorization Code).
- **Panel de estadsticas**: tus artistas y canciones ms escuchados, filtrando por periodo.
- **Generador de playlists**:
  - Modo *artistas top*: busca canciones de tus artistas ms escuchados.
  - Modo *canciones top*: usa directamente tus canciones ms escuchados.
  - Periodos: 4 semanas, 6 meses, varios aos.
- **Historial**: guarda las playlists generadas (nombre, n de canciones, enlace y fecha).

## Tecnologas

- PHP 8.3 + **Laravel 13**
- **SQLite** (base de datos por defecto)
- Tailwind CSS (va CDN)
- Spotify Web API

## Requisitos

- PHP 8.3 o superior
- Composer 2
- Una cuenta de Spotify (**Premium** para ciertas funcionalidades segn modo desarrollo)
- Una app creada en el [Spotify Developer Dashboard](https://developer.spotify.com/dashboard)

## Instalacin paso a paso (local)

Sigue estos pasos para que funcione correctamente en tu equipo:

```bash
# 1. Clonar el repositorio
git clone https://github.com/AlvaroGarcia97/spotify-stats.git
cd spotify-stats

# 2. Instalar dependencias
composer install

# 3. Copiar el archivo de entorno
# En Windows (CMD): copy .env.example .env
# En Linux/Mac: cp .env.example .env
cp .env.example .env

# 4. Generar la clave de la aplicacin
php artisan key:generate

# 5. Crear la base de datos SQLite (si no existe)
# En Windows (CMD): type nul > database\database.sqlite
# En Linux/Mac: touch database/database.sqlite
type nul > database\database.sqlite

# 6. Ejecutar las migraciones
php artisan migrate

# 7. Arrancar el servidor
php artisan serve
```

La app quedar en `http://127.0.0.1:8000`.

## Configurar la app de Spotify

1. Entra en el [Spotify Developer Dashboard](https://developer.spotify.com/dashboard) y crea una app.
2. En **Redirect URIs** aade exactamente: `http://127.0.0.1:8000/callback`
3. Copia el **Client ID** y el **Client Secret** a tu `.env`. Aade tambin estas variables:

```env
SPOTIFY_CLIENT_ID=tu_client_id
SPOTIFY_CLIENT_SECRET=tu_client_secret
SPOTIFY_REDIRECT_URI=http://127.0.0.1:8000/callback
```

Actualiza `config/services.php` ya est preparado para leerlas.

**Permisos (scopes) que pide la app:**

- `user-top-read`  leer tus artistas/canciones ms escuchados.
- `playlist-modify-public` / `playlist-modify-private`  crear y editar playlists.

> Los permisos se conceden al iniciar sesin. Si aades un scope nuevo, tienes que
> **cerrar sesin y volver a entrar** para concederlo (el token antiguo no lo incluye).
> En modo desarrollo, solo los usuarios aadidos en "Users and Access" del dashboard podrn entrar.

## Uso

1. Arranca el servidor: `php artisan serve` y abre `http://127.0.0.1:8000`.
2. Pulsa **Iniciar sesin** y autoriza la app con Spotify.
3. Ve a **Estadsticas** para ver tus tops, o a **Generador** para crear una playlist.
4. Las playlists que generes aparecern en el **Historial**.

## Solucin de problemas

- **"Invalid redirect URI"**: revisa que la URI del dashboard coincida exactamente con `SPOTIFY_REDIRECT_URI`.
- **Usuario no autorizado**: adelo en *Users and Access* mientras la app est en modo desarrollo.
- **Token caducado / sin permisos**: cierra sesin y vuelve a entrar para renovar los scopes.

## Estructura y decisiones

- `app/Services/SpotifyService.php`: **todo** el trato con la API de Spotify. Aade el token, y si caduca (401) lo **refresca** con el `refresh_token` y reintenta.
- `app/Http/Controllers/`:
  - `SpotifyAuthController`: login (redirect + callback).
  - `DashboardController`: panel de estadsticas.
  - `PlaylistGeneratorController`: generador de playlists.
- `app/Models/GeneratedPlaylist.php` + migracin: historial de playlists.

### Notas sobre la API de Spotify (2026)

- Se usa `POST /me/playlists` (el antiguo `/users/{id}/playlists` se elimin).
- Para aadir canciones: `POST /playlists/{id}/items` (antes `/tracks`, deprecado).
- El `limit` de `/search` ahora **mximo 10**.
- `audio-features` y `recommendations` estn deprecados.

## Roadmap

- [ ] Fase 2: front-end en **Angular** consumiendo una API propia.
- [ ] Tests automticos (simulando respuestas de Spotify).
- [ ] Guardar el usuario de Spotify en el historial (multi-usuario).
