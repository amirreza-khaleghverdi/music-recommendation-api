<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PlaylistTrack extends Pivot
{
    /** @use HasFactory<\Database\Factories\PlaylistTrackFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'playlist_id',
        'track_id',
        'position',
        'added_at',
    ];

    protected $casts = [
        'position' => 'integer',
        'added_at' => 'datetime',
    ];

    public function playlist() : BelongsTo {
        return $this->belongsTo(Playlist::class);
    }

    public function track() : BelongsTo {
        return $this->belongsTo(Track::class);
    }
}
