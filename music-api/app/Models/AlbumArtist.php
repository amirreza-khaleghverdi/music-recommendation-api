<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AlbumArtist extends Pivot
{
    /** @use HasFactory<\Database\Factories\AlbumArtistFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'album_id',
        'artist_id',
        'role'
    ];

    public function artist() : BelongsTo {
        return $this->belongsTo(Artist::class);
    }

    public function album() : BelongsTo {
        return $this->belongsTo(Album::class);
    }
}
