<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackLike extends Model
{
    /** @use HasFactory<\Database\Factories\TrackLikeFactory> */
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'track_id',
        'liked_at',
    ];

    protected $casts = [
        'liked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }
}
