<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->fakeAlbumTitle(),

            'album_type' => fake()->randomElement([
                'album', 'album', 'album',
                'single', 'single', 'single', 'single',
                'ep',
                'compilation',
            ]),

            'release_date' => fake()->dateTimeBetween('-60 years', 'now')->format('y-m-d'),
            'cover_image_url'=> fake()->boolean(85)
            ? 'https://picsum.photos/seed/' . fake()->uuid() . '/500/500'
            : null,
        ];
    }

    private function fakeAlbumTitle() : string {
        $patterns = [
            fn () => ucfirst(fake()->word()) . ' ' . ucfirst(fake()->word()),
            fn () => 'The ' . ucfirst(fake()->word()) . 's',
            fn () => ucfirst(fake()->word()) . ' (' . fake()->year() . ')',
            fn () => ucfirst(fake()->word()),
        ];

        return fake()->randomElement($patterns)();
    }

    // this going to be fit with states //
    public function single() : static {
        return $this->state(fn () =>['album_type' => 'single']);
    }

    public function ep() : static{
        return $this->state(fn ()=> ['album_type' => 'single']);
    }

    public function compilation() : static {
        return $this->state(fn () => ['album_type'=> 'compilation']);
    }

    public function withoutCover() : static {
        return $this->state(fn () => ['cover_image_url' => null] );
    }

    public function withArtist(?Artist $artist = null, string $role = 'primary'): static
    {
        return $this->afterCreating(function (Album $album) use ($artist, $role) {
            $album->artists()->attach(
                ($artist ?? Artist::factory()->create())->id,
                ['role' => $role]
            );  
        });
    }
}
