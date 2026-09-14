<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPlaylistTrackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('playlist')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
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
}
