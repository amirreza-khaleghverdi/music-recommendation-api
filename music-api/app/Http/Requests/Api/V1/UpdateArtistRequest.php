<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArtistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('artist')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'bio' => ['sometimes', 'nullable', 'string'],
            'country_code' => ['sometimes', 'nullable', 'string', 'size:2'],
            'formed_year' => ['sometimes', 'nullable', 'integer', 'between:1900,' . now()->year],
            'image_url' => ['sometimes', 'nullable', 'url', 'max:500'],
            'external_ids' => ['sometimes', 'nullable', 'array']
        ];
    }
}
