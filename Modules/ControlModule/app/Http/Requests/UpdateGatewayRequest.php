<?php

namespace Modules\ControlModule\Http\Requests;

use Modules\ControlModule\Models\Gateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGatewayRequest extends FormRequest
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
        $gatewayParam = $this->route('gateway')
            ?? $this->route('gateway_id')
            ?? $this->route('id')
            ?? $this->route('serial_number')
            ?? null;

        $ignoreId = $gatewayParam instanceof Gateway
            ? $gatewayParam->getKey()
            : $gatewayParam;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'external_id' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('gateways', 'external_id')
                    ->ignore($ignoreId)
                    ->whereNull('deleted_at'),
            ],
            'connection_key' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('gateways', 'connection_key')
                    ->ignore($ignoreId)
                    ->whereNull('deleted_at'),
            ],
            'location'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'ip_address'         => ['sometimes', 'nullable', 'ip'],
            'description'        => ['sometimes', 'nullable', 'string'],
            'registration_status' => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}
