<?php

namespace Modules\MapModule\Http\Requests\Fingerprint;

use Illuminate\Foundation\Http\FormRequest;

class StoreFingerprintRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'map_id' => 'required|exists:maps,id',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
