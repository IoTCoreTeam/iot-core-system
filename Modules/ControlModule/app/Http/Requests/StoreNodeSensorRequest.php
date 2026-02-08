<?php

namespace Modules\ControlModule\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNodeSensorRequest extends FormRequest
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
            'node_id' => 'required|string|max:255|exists:nodes,external_id',
            'sensor_type' => 'nullable|string|max:255',
            'last_reading' => 'nullable|numeric',
            'limit_value' => 'nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'node_id.required' => 'Please provide the node ID.',
            'node_id.string'   => 'The node ID must be a string.',
            'node_id.max'      => 'The node ID may not be greater than 255 characters.',
            'node_id.exists'   => 'The specified node ID does not exist in the system.',

            'sensor_type.string' => 'The sensor type must be a string.',
            'sensor_type.max'    => 'The sensor type may not be greater than 255 characters.',

            'last_reading.numeric' => 'The last reading must be a valid number.',
            'limit_value.numeric'  => 'The limit value must be a valid number.',
        ];
    }
}
