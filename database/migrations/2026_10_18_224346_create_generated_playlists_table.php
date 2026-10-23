<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('generated_playlists', function (Blueprint $table) {
            $table->id();
            $table->string('spotify_playlist_id');   // El id de la playlist en Spotify.
            $table->string('name');                  // Su nombre.
            $table->string('mode');                  // 'artists' o 'tracks'.
            $table->string('range');                 // '4 semanas', '6 meses'...
            $table->unsignedInteger('tracks_count'); // Cuántas canciones se añadieron.
            $table->string('url');                   // Enlace para abrirla en Spotify.
            $table->timestamps();                    // created_at y updated_at.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_playlists');
    }
};
