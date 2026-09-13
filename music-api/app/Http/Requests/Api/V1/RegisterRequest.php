<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:4', 'max:50', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::default()],
            'display_name' => ['string', 'min:4', 'max:50']
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Username is required', 
            'username.min' => 'Username is should be longer', 
            'username.max' => 'Username is should be shorter', 
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'Password is required.',
            'display_name.required' => 'Display Name is required',
            'display_name.min' => 'Username is should be longer',
            'display_name.max' => 'Username is should be shorter',
            'role.required' => 'Role is required',
            'role.in' => 'Enter a valid role'
        ];
    }
}
