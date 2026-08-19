<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genre extends Model
{
    /** @use HasFactory<\Database\Factories\GenreFactory> */
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'parent_genre_id'
    ];

    public function tracks() : BelongsToMany {
        return $this->belongsToMany(Track::class, "track_genre");
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'parent_genre_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Genre::class, 'parent_genre_id');
    }
}
