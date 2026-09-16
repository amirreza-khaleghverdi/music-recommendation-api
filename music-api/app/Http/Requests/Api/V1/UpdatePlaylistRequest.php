<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('playlist')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:150',
            Rule::unique('playlists')->where(
                fn ($query) => $query->where('user_id', $this->user()->id)
                ->ignore($this->route('playlist'))
            )],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_public' => ['sometimes', 'nullable', 'boolean'],
            'cover_image' => ['sometimes', 'nullable', 'url', 'max:500']
        ];
    }
}
