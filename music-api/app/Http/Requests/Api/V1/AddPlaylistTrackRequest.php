<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class AddPlaylistTrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('playlist')) ?? false;
    }

    public function rules(): array
    {
        return [
            'track_id' => ['required', 'integer', Rule::exists('tracks', 'id'),
                Rule::unique('playlist_track', 'track_id')->where(
                    fn($query) => $query->where('playlist_id', $this->route('playlist')->id)
                ),
            ],

            'position' => ['nullable', 'integer', 'min:1']
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'track_id.required' => 'Track is required.',
            'track_id.exists' => 'Enter a valid track.',
            'track_id.unique' => 'Track is already in playlist.',
            
            'position.min' => 'Position must be at least 1',
            'position.integer' => 'Position should be a number.'
        ];
    }
}
