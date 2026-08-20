<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackAudioFeature extends Model
{
    /** @use HasFactory<\Database\Factories\TrackAudioFeaturesFactory> */
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'track_id';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'track_id', 'tempo_bpm', 'energy', 'danceability', 'valence',
        'acousticness', 'instrumentalness', 'speechiness', 'liveness',
        'loudness_db', 'key_signature', 'mode', 'time_signature',
    ];

    protected $casts = [
        'tempo_bpm'        => 'float',
        'energy'            => 'float',
        'danceability'      => 'float',
        'valence'           => 'float',
        'acousticness'      => 'float',
        'instrumentalness'  => 'float',
        'speechiness'       => 'float',
        'liveness'          => 'float',
        'loudness_db'       => 'float',
        'key_signature'     => 'integer',
        'mode'              => 'integer',
        'time_signature'    => 'integer',
    ];

    public function track() : BelongsTo {
        return $this->belongsTo(Track::class);
    }
}
