<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\AlbumArtist;
use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;
use PHPUnit\Runner\TestRunHistory\TestRunHistory;

/**
 * @extends Factory<AlbumArtist>
 */
class AlbumArtistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_id' => Album::factory(),
            'artist_id' => Artist::factory(),
            'role' => ['primary', 'featured', 'various'] 
        ];
    }

    public function withPrimary() : static {
        return $this->state(fn() => ['role' => 'primary']);
    }

    public function withFeatured() : static {
        return $this->state(fn() => ['role' => 'featured'] );
    }

    public function withVarious() : static {
        return $this->state(fn() => ['role' => 'various'] );
    }

    public function forAlbum(Album $album) : static {
        return $this->state(fn() => ['album_id' => $album->id] );
    }

    public function forArtist(Artist $artist) : static {
        return $this->state(fn() => ['artist_id' => $artist->id] );
    }
}
