<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Playlist;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class StorePlaylistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Playlist::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150',
            Rule::unique('playlist')->where(
                fn ($query) => $query->where('user_id', $this->user()->id)
            )],
            'description' => ['nullable', 'string'],
            'is_public' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'url', 'max:500']
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'title.required' => 'Title is required',
            'title.string' => 'Title should be string',
            'title.max' => 'Title should be shorter than 150 charecter',
            'title.unique' => 'You already have a playlist with that title.',

            'description.string' => 'Description should be string',

            'is_public.boolean' => 'This field is boolean',

            'cover_image.url' => 'Enter a valid url',
            'cover_image.max' => 'Url should be less than 500 charecters'
        ];
    }
}
