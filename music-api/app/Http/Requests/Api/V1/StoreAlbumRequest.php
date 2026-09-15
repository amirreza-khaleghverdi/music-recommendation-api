<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Album;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class StoreAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Album::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'album_type' => ['required', Rule::in(['album', 'single', 'ep', 'compilation'])],
            'release_date' => ['nullable', 'date'],
            'cover_image_url' => ['nullable', 'url', 'max:500']
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'title.required' => 'Title is required',
            'title.string' => 'Title should be string',
            'title.max' => 'Title cannot be longer than 200 charecters',

            'album_type.required' => 'Album type is required',
            'album_type.in' => 'Invalid album type',

            'release_date.date' => 'Release date should be a valid date',
            
            'cover_image_url.url' => 'Cover image must be a valid URL',
            'cover_image_url.max' => 'Cover image URL cannot be longer than 500 characters'
        ];
    }
}
