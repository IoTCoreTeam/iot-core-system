<?php

namespace Modules\ControlModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreControlUrlRequest extends FormRequest
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
            'controller_id' => ['required', 'string', 'max:255'],
            'node_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:2048'],
            'input_type' => ['required', 'string', 'max:100'],
        ];
    }
}
