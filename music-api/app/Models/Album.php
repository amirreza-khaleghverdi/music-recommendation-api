<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Album extends Model
{
    /** @use HasFactory<\Database\Factories\AlbumFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'album_type',
        'release_date',
        'cover_image_url'
    ];

    protected $casts = [
        'release_date'=> 'date'
    ];

    public function artists() : BelongsToMany {
        return $this->belongsToMany(Artist::class, 'album_artist')
        ->using(AlbumArtist::class)
        ->withPivot('role');
    }

    public function tracks() : HasMany {
        return $this->hasMany(Track::class);
    }

    public function listenEvents(): HasManyThrough
    {
        return $this->hasManyThrough(ListenEvent::class, Track::class);
    }
}
