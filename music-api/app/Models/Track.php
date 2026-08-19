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
    /** @use HasFactory<\Database\Factories\TrackFactory> */
    use HasFactory;
    protected $fillable = [
        'album_id',
        'title',
        'track_number',
        'disc_number',
        'duration_ms',
        'isrc',
        'explicit',
        'external_ids'
    ];

    protected $casts = [
        'external_ids'=> 'array',
        'track_number'=> 'integer',
        'disc_number'=> 'integer',
        'duration_ms'=> 'integer',
        'explicit'=> 'boolean'
    ];


    public function album(): BelongsTo{
        return $this->belongsTo(Album::class);
    }

    public function artists(): BelongsToMany{
        return $this->belongsToMany(Artist::class, 'track_artist')
            ->withPivot('role'); 
        }

    public function genres(): BelongsToMany{
        return $this->belongsToMany(Genre::class, 'track_genre');
    }

    public function audioFeatures(): HasOne{
        return $this->hasOne(TrackAudioFeature::class);
    }

    public function listenEvents(): HasMany{
        return $this->hasMany(ListenEvent::class);
    }

    public function likedByUsers(): BelongsToMany{
        return $this->belongsToMany(User::class, 'track_likes')
            ->withPivot('liked_at');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(TrackLike::class);
    }
}
