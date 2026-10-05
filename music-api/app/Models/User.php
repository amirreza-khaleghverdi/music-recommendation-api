<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'display_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'role' => UserRole::class
    ];


    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class);
    }

    public function trackLikes(): HasMany
    {
        return $this->hasMany(TrackLike::class);
    }

    public function likedTracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_likes')
            ->withPivot('liked_at');
    }

    public function artistFollows(): HasMany
    {
        return $this->hasMany(ArtistFollow::class);
    }

    public function followedArtists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_follows')
            ->withPivot('followed_at');
    }

    public function listenEvents(): HasMany
    {
        return $this->hasMany(ListenEvent::class);
    }
}
