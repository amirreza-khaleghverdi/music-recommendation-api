<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistFollow extends Model
{
    /** @use HasFactory<\Database\Factories\ArtistFollowFactory> */
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'artist_id',
        'followed_at'
    ];

    protected $casts = [
        'followed_at'=> 'datetime'
    ];

    public function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function artist() : BelongsTo {
        return $this->belongsTo(Artist::class);
    }
}
