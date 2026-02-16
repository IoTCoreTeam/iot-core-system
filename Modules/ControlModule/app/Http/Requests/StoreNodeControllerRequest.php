<?php

namespace Modules\ControlModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNodeControllerRequest extends FormRequest
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
            'node_id' => 'required|string|max:255|exists:nodes,id',
            'firmware_version' => 'nullable|string|max:255',
            'control_url' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'node_id.required' => 'Please provide the node ID.',
            'node_id.string'   => 'The node ID must be a string.',
            'node_id.max'      => 'The node ID may not be greater than 255 characters.',
            'node_id.exists'   => 'The specified node ID does not exist in the system.',

            'firmware_version.string' => 'The firmware version must be a string.',
            'firmware_version.max'    => 'The firmware version may not be greater than 255 characters.',

            'control_url.string' => 'The control URL must be a string.',
            'control_url.max'    => 'The control URL may not be greater than 255 characters.',
        ];
    }
}
