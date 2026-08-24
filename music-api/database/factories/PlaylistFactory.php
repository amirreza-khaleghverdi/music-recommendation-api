<?php

namespace Database\Factories;

use App\Models\Playlist;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Playlist>
 */
class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->fakePlaylistTitle(),
            'description' => fake()->text(200),
            'cover_image' => fake()->imageUrl(),
        ];
    }

    private function fakePlaylistTitle(): string
    {
        $patterns = [
            fn () => ucfirst(fake()->word()) . ' ' . fake()->randomElement(['Vibes', 'Mix', 'Sessions', 'Hits', 'Radio']),
            fn () => fake()->randomElement(['Late Night', 'Morning', 'Weekend', 'Summer', 'Road Trip']) . ' ' . fake()->randomElement(['Mix', 'Playlist', 'Jams']),
            fn () => 'My ' . ucfirst(fake()->word()) . ' Playlist',
        ];

        return fake()->randomElement($patterns)();
    }

    public function withoutCoverImage(): static
    {
        return $this->state(fn () => ['cover_image' => null]);
    }

    public function public(): static
    {
        return $this->state(fn () => ['is_public' => true]);
    }

    public function withTracks(array $tracks = []): static
    {
        return $this->afterCreating(function (Playlist $playlist) use ($tracks) {
            $tracks = empty($tracks) ? [Track::factory()->create()] : $tracks;

            foreach ($tracks as $index => $track) {
                $playlist->tracks()->attach($track->id, ['position' => $index + 1]);
            }
        });
    }
}