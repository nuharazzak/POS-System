<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'category_id'     => $this->category_id,
            'category_name'   => $this->category ? $this->category->name : null,
            'name'            => $this->name,
            'description'     => $this->description,
            'price'           => (float) $this->price,
            'stock_quantity'  => (int) $this->stock_quantity,
            'image'           => $this->image,
            'is_active'       => (bool) $this->is_active,
            'is_low_stock'    => $this->is_low_stock,
            'is_out_of_stock' => $this->is_out_of_stock,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
