<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'    => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name'           => ['sometimes', 'required', 'string', 'max:150'],
            'description'    => ['nullable', 'string'],
            'price'          => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'image'          => ['nullable', 'string'],
            'is_active'      => ['nullable', 'boolean'],
        ];
    }
}
