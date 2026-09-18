<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordListenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $containedContexts = ['playlist', 'album', 'artist'];
        $contextType = $this->input('context_type');
        $hasContainer = in_array($contextType, $containedContexts, true);

        return [
            'ms_played' => ['required', 'integer', 'min:0'],
            'context_type' => [
                'nullable',
                Rule::in(['playlist', 'album', 'artist', 'search', 'radio', 'recommendation']),
            ],
            'context_id' => [
                Rule::requiredIf($hasContainer),
                Rule::prohibitedIf(! $hasContainer),
                'integer',
                Rule::when($contextType === 'playlist', 'exists:playlists,id'),
                Rule::when($contextType === 'album', 'exists:albums,id'),
                Rule::when($contextType === 'artist', 'exists:artists,id'),
            ],
            'device_type' => ['nullable', 'string', 'max:30'],
            'session_id' => ['nullable', 'uuid'],
        ];
    }
}
