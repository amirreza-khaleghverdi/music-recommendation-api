<?php

namespace Database\Factories;

use App\Models\Track;
use App\Models\TrackAudioFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackAudioFeature>
 */
class TrackAudioFeatureFactory extends Factory
{
    public function definition(): array
    {
        return [
            'track_id' => Track::factory(),
            'tempo_bpm' => fake()->randomFloat(2, 60, 200),
            'energy' => fake()->randomFloat(3, 0, 1),
            'danceability' => fake()->randomFloat(3, 0, 1),
            'valence' => fake()->randomFloat(3, 0, 1),
            'acousticness' => fake()->randomFloat(3, 0, 1),
            'instrumentalness' => fake()->randomFloat(3, 0, 1),
            'speechiness' => fake()->randomFloat(3, 0, 1),
            'liveness' => fake()->randomFloat(3, 0, 1),
            // Most produced music sits around -20 to -3 dB; -60 is near-silence,
            // 0 is near-clipping — both real but rare.
            'loudness_db' => fake()->randomFloat(2, -20, -3),
            'key_signature' => fake()->numberBetween(0, 11),
            'mode' => fake()->randomElement([0, 1]),
            // Real music overwhelmingly uses 4/4 time — a uniform 1-12
            // distribution would be unrealistic for the same reason album_type
            // and time_signature-adjacent fields elsewhere needed weighting.
            'time_signature' => fake()->randomElement([4, 4, 4, 4, 3, 3, 6, 5]),
        ];
    }

    public function forTrack(Track $track) : static {
        return $this->state(fn() => ['track_id' => $track->id]);
    }
}
