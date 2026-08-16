<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TrackArtist extends Pivot
{
    /** @use HasFactory<\Database\Factories\TrackArtistFactory> */
    use HasFactory;
}
