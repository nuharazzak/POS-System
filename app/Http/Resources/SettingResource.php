<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'store_name'          => $this->store_name,
            'address'             => $this->address,
            'phone'               => $this->phone,
            'currency'            => $this->currency,
            'tax_rate'            => (float) $this->tax_rate,
            'low_stock_threshold' => (int) $this->low_stock_threshold,
            'created_at'          => $this->created_at?->toIso8601String(),
            'updated_at'          => $this->updated_at?->toIso8601String(),
        ];
    }
}
