<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AlbumArtist extends Pivot
{
    /** @use HasFactory<\Database\Factories\AlbumArtistFactory> */
    use HasFactory;
}
