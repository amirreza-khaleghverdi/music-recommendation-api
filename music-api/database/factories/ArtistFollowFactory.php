<?php

namespace Database\Factories;

use App\Models\Artist;
use App\Models\ArtistFollow;
use App\Models\User;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ArtistFollow>
 */
class ArtistFollowFactory extends Factory
{
    protected $model = ArtistFollow::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'artist_id' => Artist::factory(),
            'followed_at' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }

    public function withFollowedAt(DateTime $followedAt): static
    {
        return $this->state(fn () => ['followed_at' => $followedAt]);
    }
}