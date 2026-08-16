<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlaylistTrack extends Pivot
{
    /** @use HasFactory<\Database\Factories\PlaylistTrackFactory> */
    use HasFactory;
}
