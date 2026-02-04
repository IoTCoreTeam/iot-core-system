<?php

namespace Modules\MapModule\Http\Requests\Area;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAreaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'height_m' => 'nullable|numeric|min:0',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
