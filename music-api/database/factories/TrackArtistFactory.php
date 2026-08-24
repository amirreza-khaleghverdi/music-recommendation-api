<?php

namespace Database\Factories;

use App\Models\Artist;
use App\Models\Track;
use App\Models\TrackArtist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackArtist>
 */
class TrackArtistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'track_id' => Track::factory(),
            'artist_id' => Artist::factory(),
            'role' => fake()->randomElement( ['primary', 'featured', 'producer', 'composer', 'remixer'])
        ];
    }
}
