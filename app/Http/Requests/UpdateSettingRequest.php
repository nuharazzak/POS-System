<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name'          => ['required', 'string', 'max:100'],
            'address'             => ['nullable', 'string', 'max:255'],
            'phone'               => ['nullable', 'string', 'max:50'],
            'currency'            => ['required', 'string', 'max:10'],
            'tax_rate'            => ['required', 'numeric', 'min:0', 'max:100'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
        ];
    }
}
