<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:4', 'max:50', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:250', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'display_name' => ['nullable', 'string', 'min:4', 'max:100']
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username is required', 
            'username.min' => 'Username should be longer', 
            'username.max' => 'Username should be shorter', 
            'username.unique' => 'This username has been used.',

            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'email.unique' => 'This email has already registered.',

            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirm does not match.',
            
            'display_name.min' => 'Username should be longer',
            'display_name.max' => 'Username should be shorter',
        ];
    }
}
