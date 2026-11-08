<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'quantity'         => $this->quantity,
            'reference_number' => $this->reference_number,
            'notes'            => $this->notes,
            'created_at'       => $this->created_at?->toIso8601String(),
            'created_human'    => $this->created_at?->diffForHumans(),
            'product'          => $this->whenLoaded('product', fn () => [
                'id'   => $this->product->id,
                'name' => $this->product->name,
                'sku'  => $this->product->sku,
                'url'  => route('products.show', $this->product->id),
            ]),
            'user'             => $this->whenLoaded('user', fn () => [
                'id'   => $this->user->id,
                'name' => $this->user->name,
            ]),
            'url'              => route('transactions.show', $this->id),
        ];
    }
}