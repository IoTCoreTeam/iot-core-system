<?php

namespace Modules\MapModule\Http\Requests\AccessPoint;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccessPointRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ssid' => 'nullable|string|max:100',
            'bssid' => 'required|string|max:50|unique:access_points,bssid',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
