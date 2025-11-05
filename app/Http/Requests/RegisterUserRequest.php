<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // cho phép tất cả người dùng truy cập
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'sometimes|string',
            'description' => 'sometimes|string|nullable',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
