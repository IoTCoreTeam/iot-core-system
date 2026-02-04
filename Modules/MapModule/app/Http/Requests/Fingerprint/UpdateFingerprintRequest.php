<?php

namespace Modules\MapModule\Http\Requests\Fingerprint;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFingerprintRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'map_id' => 'sometimes|exists:maps,id',
            'x' => 'sometimes|numeric',
            'y' => 'sometimes|numeric',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
