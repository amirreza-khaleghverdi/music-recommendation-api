<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('album')) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:200'],
            'album_type' => ['sometimes', Rule::in(['album', 'single', 'ep', 'compilation'])],
            'release_date' => ['sometimes', 'nullable', 'date'],
            'cover_image_url' => ['sometimes', 'nullable', 'url', 'max:500']
        ];
    }
}
