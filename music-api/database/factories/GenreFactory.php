<?php

namespace Database\Factories;

use App\Models\Genre;
use App\Models\Track;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Genre>
 */
class GenreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Rock', 'Pop', 'Hip Hop', 'Jazz', 'Classical', 'Electronic',
                'Country', 'R&B', 'Reggae', 'Blues', 'Folk', 'Metal',
                'Punk', 'Soul', 'Funk', 'Indie', 'Ambient', 'House',
                'Techno', 'Dubstep', 'Latin', 'K-Pop', 'Gospel', 'Alternative',
            ]),
        ];
    }

    public function name(string $name) : static {
        return $this->state(fn() => ['name'=> $name]);
    }

    public function withTrack(?Track $track=null) : static {
        return $this->afterCreating(function (Genre $genre) use ($track) {
            $track ?? track::factory()->create()->genres()->attach($genre->id);
        }
    );
    }
}
