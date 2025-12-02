<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSystemLogRequest extends FormRequest
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
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['required', 'string', 'max:120'],
            'level' => ['nullable', 'string', 'in:debug,info,notice,warning,error,critical,alert,emergency'],
            'message' => ['nullable', 'string'],
            'context' => ['nullable', 'array'],
            'ip_address' => ['nullable', 'ip'],
        ];
    }
}
