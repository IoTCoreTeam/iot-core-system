<?php

namespace Modules\MapModule\Http\Requests\Map;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMapRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'area_id' => 'sometimes|exists:areas,id',
            'name' => 'sometimes|string|max:100',
            'image_url' => 'nullable|string|max:255',
            'width_px' => 'nullable|integer|min:1',
            'height_px' => 'nullable|integer|min:1',
            'scale_m_per_px' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
