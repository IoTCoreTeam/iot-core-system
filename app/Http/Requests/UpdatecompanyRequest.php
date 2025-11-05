<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Company;

class UpdatecompanyRequest extends FormRequest
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
    public function rules()
    {
        $companyId = Company::query()->value('id');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('companies', 'email')->ignore($companyId),
            ],
            'phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'This email already exists.',
            'phone.max' => 'Phone number must not exceed 20 characters.',
            'fax.max' => 'Fax number must not exceed 20 characters.',
            'address.max' => 'Address must not exceed 255 characters.',
        ];
    }
}
