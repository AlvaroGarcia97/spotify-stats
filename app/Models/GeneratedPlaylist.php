<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedPlaylist extends Model
{
    // Columnas que se pueden rellenar de golpe con create([...]).
    protected $fillable = [
        'spotify_playlist_id',
        'name',
        'mode',
        'range',
        'tracks_count',
        'url',
    ];
}
