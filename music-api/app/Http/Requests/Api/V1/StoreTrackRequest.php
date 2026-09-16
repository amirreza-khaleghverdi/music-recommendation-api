<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Track;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class StoreTrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Track::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'album_id' => ['required', 'integer', 'exists:albums,id'],
            'title' => ['required', 'string', 'max:200'],
            'track_number' => ['nullable', 'integer'],
            'disc_number' => ['nullable', 'integer'],
            'duration_ms' => ['required', 'integer', 'min:1'],
            'isrc' => ['nullable', 'string', 'max:15', Rule::unique('tracks', 'isrc')],
            'explicit' => ['nullable', 'boolean'],
            'external_ids' => ['nullable', 'array']
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'album_id.required' => 'Album ID is required',
            'album_id.integer' => 'Album ID should be integer',
            'album_id.exists' => 'Enter a valid album',

            'title.required' => 'Title is required',
            'title.string' => 'Enter a valid title',
            'title.max' => 'Title should be shorter than 200 charectrs',

            'track_number.string' => 'Enter a valid track number',
            'disc_number.string' => 'Enter a valid disc number',

            'duration_ms.required' => 'Duration is required',
            'duration.ms' => 'Enter a valid duration',
            'duration_ms.min' => 'Duration must be greater than 0',

            'isrc.string' => 'Enter a valid isrc',
            'isrc.max' => 'Isrc should be shorter than 15 charectres',
            'isrc.unique' => 'Isrc should be unique',

            'explicit.boolean' => 'Enter a valid Explicit',
            'external_ids.array' => 'External IDs must be a valid structured value'
        ];
    }
}
