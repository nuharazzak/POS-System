<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'order_number'    => $this->order_number,
            'user_id'         => $this->user_id,
            'user_name'       => $this->user ? $this->user->name : 'Cashier',
            'subtotal'        => (float) $this->subtotal,
            'discount_type'   => $this->discount_type,
            'discount_value'  => (float) $this->discount_value,
            'discount_amount' => (float) $this->discount_amount,
            'tax_rate'        => (float) $this->tax_rate,
            'tax_amount'      => (float) $this->tax_amount,
            'total_amount'    => (float) $this->total_amount,
            'payment_method'  => $this->payment_method,
            'amount_received' => (float) $this->amount_received,
            'change_amount'   => (float) $this->change_amount,
            'status'          => $this->status,
            'created_at'      => $this->created_at?->toIso8601String(),
            'items'           => OrderItemResource::collection($this->whenLoaded('items', $this->items)),
        ];
    }
}
