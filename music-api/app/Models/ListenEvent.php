<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListenEvent extends Model
{
    /** @use HasFactory<\Database\Factories\ListenEventsFactory> */
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'track_id',
        'played_at',
        'ms_played',
        'context_type',
        'context_id',
        'device_type',
        'session_id'
    ];

    protected $casts = [
        'played_at'=> 'datetime',
        'ms_played'=> 'integer',
        'context_id'=> 'integer'
    ];

    public function track() : BelongsTo {
        return $this->belongsTo(Track::class);
    }

    public function user() : BelongsTo {
        return $this->belongsTo(User::class);
    }
}
