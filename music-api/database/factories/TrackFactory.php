<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Genre;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $album = Album::factory()->create();
        return [
            'album_id' => $album->id,
            'title' => $this->fakeTrackTitle(),
            'track_number' => fake()->numberBetween(1, 20),
            'disc_number' => fake()->randomElement([1, 1, 1, 1, 1, 1, 1, 1, 2]),
            'duration_ms' => fake()->numberBetween(90_000, 480_000),
            'isrc' => fake()->boolean(60) ? $this->fakeIsrc() : null,
            'explicit'=>fake()->boolean(45),
            'external_ids' => [
            'spotify' => fake()->uuid(),
            'isrc_source' => 'kaggle',
            ],
        ];
    }

    private function fakeTrackTitle(): string
    {
        $patterns = [
            fn () => ucfirst(fake()->word()),
            fn () => ucfirst(fake()->word()) . ' ' . ucfirst(fake()->word()),
            fn () => ucfirst(fake()->word()) . ' (feat. ' . fake()->firstName() . ')',
        ];

        return fake()->randomElement($patterns)();
    }

    private function fakeIsrc(): string
    {
        // Real ISRC format: CC-XXX-YY-NNNNN (country, registrant, year, designation)
        return strtoupper(fake()->countryCode())
            . fake()->regexify('[A-Z0-9]{3}')
            . fake()->numerify('##')
            . fake()->numerify('#####');
    }

    public function withArtist(?Artist $artist=null, $role='primary') : static {
        return $this->afterCreating(function (Track $track) use ($artist, $role) {
            $track->artists()->attach(
                ($artist ?? Artist::factory()->create())->id,
                ['role' => $role]
            );
        });
    }

    public function withGenre(?Genre $genre=null) : static {
        return $this->afterCreating(function (Track $track) use ($genre) {
            $track->genres()->attach(($genre ?? Genre::factory()->create())->id);
        });
    }

    public function withUser(?User $user =null) : static {
        return $this->afterCreating(function (Track $track) use ($user) {
            $track->likedByUsers()->attach(($user ?? User::factory()->create())->id);
        });
    }

    public function forAlbum(Album $album) : static {
        return $this->state(fn() => ['album_id' => $album->id] );
    }
}
