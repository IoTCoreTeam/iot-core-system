<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemLogRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'action' => ['sometimes', 'required', 'string', 'max:120'],
            'level' => ['sometimes', 'nullable', 'string', 'in:debug,info,notice,warning,error,critical,alert,emergency'],
            'message' => ['sometimes', 'nullable', 'string'],
            'context' => ['sometimes', 'nullable', 'array'],
            'ip_address' => ['sometimes', 'nullable', 'ip'],
        ];
    }
}
