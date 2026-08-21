<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Artist>
 */
class ArtistFactory extends Factory
{
    protected $model = Artist::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'bio' => fake()->text(200),
            'country_code' => fake()->countryCode(),
            'formed_year' => fake()->numberBetween(1950, (int) date('Y')),
            'image_url' => fake()->imageUrl(),
            'external_ids' => [
                'spotify' => fake()->uuid(),
                'musicbrainz' => fake()->uuid(),
            ],
        ];
    }

    public function withoutBio(): static
    {
        return $this->state(fn () => ['bio' => null]);
    }

    public function withoutCountryCode(): static
    {
        return $this->state(fn () => ['country_code' => null]);
    }

    public function withoutFormedYear(): static
    {
        return $this->state(fn () => ['formed_year' => null]);
    }

    public function withoutImageUrl(): static
    {
        return $this->state(fn () => ['image_url' => null]);
    }

    public function withoutExternalIds(): static
    {
        return $this->state(fn () => ['external_ids' => null]);
    }

    public function withAlbum(?Album $album = null, string $role = 'primary'): static
    {
        return $this->afterCreating(function (Artist $artist) use ($album, $role) {
            $artist->albums()->attach(
                ($album ?? Album::factory()->create())->id,
                ['role' => $role]
            );
        });
    }

    public function withTrack(?Track $track = null, string $role = 'primary'): static
    {
        return $this->afterCreating(function (Artist $artist) use ($track, $role) {
            $artist->tracks()->attach(
                ($track ?? Track::factory()->create())->id,
                ['role' => $role]
            );
        });
    }
}