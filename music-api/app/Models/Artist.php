<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    /** @use HasFactory<\Database\Factories\ArtistFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'bio',
        'country_code',
        'formed_year',
        'image_url',
        'external_ids'
    ];

    protected $casts = [
        'formed_year'=> 'integer',
        'external_ids'=> 'array'
    ];

    public function tracks() : BelongsToMany {
        return $this->belongsToMany(Track::class, 'track_artist')
        ->using(TrackArtist::class)
        ->withPivot('role');
    }

    public function albums() : BelongsToMany {
        return $this->belongsToMany(Album::class, 'album_artist')
        ->using(AlbumArtist::class)
        ->withPivot('role');
    }
}
