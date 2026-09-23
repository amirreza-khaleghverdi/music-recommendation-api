<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('track')) ?? false;
    }

    public function rules(): array
    {
        return [
            'album_id' => ['sometimes', 'integer', 'exists:albums,id'],
            'title' => ['sometimes', 'string', 'max:200'],
            'track_number' => ['sometimes', 'nullable', 'integer'],
            'disc_number' => ['sometimes', 'nullable', 'integer'],
            'duration_ms' => ['sometimes', 'integer', 'min:1'],
            'isrc' => ['sometimes', 'nullable', 'string', 'max:15', Rule::unique('tracks', 'isrc')->ignore($this->route('track'))],
            'explicit' => ['sometimes', 'nullable', 'boolean'],
            'external_ids' => ['sometimes', 'nullable', 'array']
        ];
    }
}
