<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class AddAlbumArtistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('album')) ?? false;
    }

    public function rules(): array
    {
        return [
            'artist_id' => ['required', 'integer', 'exists:artists,id',
        
                Rule::unique('album_artist', 'artist_id')->where(
                    fn ($query) => $query
                    ->where('album_id', $this->route('album')->id)
                    ->where('role', $this->input('role'))
                )
            ],
            'role' => ['required', Rule::in(['primary', 'featured', 'various'])]
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'artist_id.required' => 'Artist id is required',
            'artist_id.exists' => 'Enter a valid artist.',
            'artist_id.unique' => 'This artist already has that role on this album.',

            'role.required' => 'Role is required',
            'role.in' => 'Enter a valid role'
        ];
    }
}
