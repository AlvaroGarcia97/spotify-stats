<?php

// "Exceptions" es la carpeta de los errores propios de nuestra app.
namespace App\Exceptions;

use RuntimeException; // Una excepción genérica de PHP ("algo salió mal").

// Nuestra excepción para cuando la API de Spotify responde con error.
class SpotifyException extends RuntimeException
{
}
