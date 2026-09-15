<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Artist;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreArtistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Artist::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'bio' => ['nullable', 'string'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'formed_year' => ['nullable', 'integer', 'between:1900,' . now()->year],
            'image_url' => ['nullable', 'url', 'max:500'],
            'external_ids' => ['nullable', 'array']
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name should be shorter than 150 characters',

            'country_code.string' => 'Country code should be text',
            'country_code.size' => 'Country code must be exactly 2 characters',

            'formed_year.integer' => 'Formed year should be a number',
            'formed_year.between' => 'Formed year must be between 1900 and the current year',

            'image_url.url' => 'Enter a valid URL',
            'image_url.max' => 'URL should be shorter than 500 characters',

            'external_ids.array' => 'External IDs must be a valid structured value'
        ];
    }
}
