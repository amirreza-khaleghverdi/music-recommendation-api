<?php

namespace Database\Factories;

use App\Models\ListenEvent;
use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ListenEvent>
 */
class ListenEventFactory extends Factory
{
    public function definition(): array
    {
        $track = Track::factory()->create();

        return [
            'user_id' => User::factory(),
            'track_id' => $track->id,
            'played_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'ms_played' => $this->realisticMsPlayed($track->duration_ms),
            'context_type' => null,
            'context_id' => null,
            'device_type' => fake()->randomElement(['mobile', 'desktop', 'web', 'tablet', 'smart_speaker']),
            'session_id' => fake()->uuid()
        ];
    }

    private function realisticMsPlayed(int $durationMs): int
    {
        // 70% of plays: mostly complete (70-100% of track)
        if (fake()->boolean(70)) {
            return (int) ($durationMs * fake()->randomFloat(2, 0.7, 1.0));
        }

        // 30% of plays: early skip (2-40% of track)
        return (int) ($durationMs * fake()->randomFloat(2, 0.02, 0.4));
    }

    public function completed() : static {
        return $this->state(function (array $attribute) {
            $track = Track::find($attribute['track_id']);
            return ['ms_played' => $track->duration_ms];
        });
    }

    public function skipped() : static {
        return $this->state(function (array $attribute) {
            $track = Track::find($attribute['track_id']);
            return ['ms_played' => (int) ($track->duration_ms* fake()->randomFloat(2, 0.02, 0.15))];
        });
    }


    public function inPlaylist(int $playlistId) : static {
        return $this->state([
            'context_type' => 'playlist',
            'context_id' => $playlistId
        ]);
    }

    public function inAlbum(int $albumID) : static {
        return $this->state([
            'context_type' => 'album',
            'context_id' => $albumID
        ]);
    }

    public function inArtist(int $artistID) : static {
        return $this->state([
            'context_type' => 'artist',
            'context_id' => $artistID
        ]);
    }

    public function viaRecommendation() : static {
        return $this->state([
            'context_type' => 'recommendation',
            'context_id' => null
        ]);
    }

    public function viaSearch(): static
    {
        return $this->state(fn () => [
            'context_type' => 'search',
            'context_id' => null,
        ]);
    }

    public function viaRadio(): static
    {
        return $this->state(fn () => [
            'context_type' => 'radio',
            'context_id' => null,
        ]);
    }

    public function forTrack(Track $track): static
    {
        return $this->state(fn () => [
            'track_id' => $track->id,
            'ms_played' => $this->realisticMsPlayed($track->duration_ms),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
    
}
