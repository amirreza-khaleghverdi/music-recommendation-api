<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReorderPlaylistTracksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('playlist')) ?? false;
    }

    public function rules(): array
    {
        return [
            'track_ids' => ['required', 'array'],
            'track_ids.*' => ['required', 'integer', 'exists:tracks,id', 'distinct'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $playlist = $this->route('playlist');

            $currentIds = $playlist->tracks()->pluck('tracks.id')->sort()->values()->all();
            $submittedIds = collect($this->input('track_ids', []))->sort()->values()->all();

            if ($currentIds !== $submittedIds) {
                $validator->errors()->add(
                    'track_ids',
                    'track_ids must contain exactly the tracks currently in the playlist — use the add/remove endpoints to change membership, not reorder.'
                );
            }
        });
    }
}
