<?php

namespace Modules\MapModule\Http\Requests\AccessPoint;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccessPointRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ssid' => 'nullable|string|max:100',
            'bssid' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('access_points', 'bssid')->ignore($this->access_point),
            ],
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
