<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id',
        'title',
        'track_number',
        'disc_number',
        'duration_ms',
        'isrc',
        'explicit',
        'external_ids',
    ];
    // search_vector intentionally excluded — DB-generated (STORED tsvector),
    // Eloquent must never attempt to write it

    protected $casts = [
        'external_ids' => 'array',   // jsonb
        'explicit'     => 'boolean', // pdo_pgsql can return "t"/"f" as string
        'duration_ms'  => 'integer',
        'track_number' => 'integer',
        'disc_number'  => 'integer',
    ];

    // --- Relationships ---

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'track_artist')
            ->withPivot('role'); // primary, featured, producer, composer, remixer
            // no withTimestamps() — track_artist has no created_at/updated_at columns
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'track_genre');
        // plain pivot, no extra columns — matches your migration exactly
    }

    public function audioFeatures(): HasOne
    {
        return $this->hasOne(TrackAudioFeature::class);
        // track_audio_features.track_id is itself the PK (1:1, not 1:many)
    }

    public function listenEvents(): HasMany
    {
        return $this->hasMany(ListenEvent::class);
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'track_likes')
            ->withPivot('liked_at');
            // no withTimestamps() — track_likes has liked_at, not created_at/updated_at
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'playlist_track')
            ->using(PlaylistTrackPivot::class)
            ->withPivot(['position', 'added_at'])
            ->orderByPivot('position');
            // no withTimestamps() — playlist_track has added_at, not created_at/updated_at
    }
}