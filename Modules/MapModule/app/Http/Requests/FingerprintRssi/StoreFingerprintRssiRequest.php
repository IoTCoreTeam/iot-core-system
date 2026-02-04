<?php

namespace Modules\MapModule\Http\Requests\FingerprintRssi;

use Illuminate\Foundation\Http\FormRequest;

class StoreFingerprintRssiRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fingerprint_id' => 'required|exists:fingerprints,id',
            'access_point_id' => 'required|exists:access_points,id',
            'rssi' => 'required|integer',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
