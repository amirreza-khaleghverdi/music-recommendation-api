<?php

namespace Database\Factories;

use App\Models\Track;
use App\Models\TrackLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackLike>
 */
class TrackLikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'track_id' => Track::factory(),
            'liked_at' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
