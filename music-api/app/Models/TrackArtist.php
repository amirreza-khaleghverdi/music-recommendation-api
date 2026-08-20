<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TrackArtist extends Pivot
{
    /** @use HasFactory<\Database\Factories\TrackArtistFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'track_id',
        'artist_id',
        'role'
    ];

    public function track() : BelongsTo {
        return $this->belongsTo(Track::class);
    }

    public function artist() : BelongsTo {
        return $this->belongsTo(Artist::class);
    }
}
