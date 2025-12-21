<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'menu'=> [
                'id'=> $this->menu->id,
                'name'=> $this->menu->name,
                'price'=> $this->menu->price,
            ],
            'quantity'=> $this->quantity,
            'price'=> $this->price,
            'discount'=> $this->discount,
            'created_at'=> $this->created_at->format("d m Y"),
            'updated_at'=> $this->updated_at->format("d m Y"),
        ];
    }
}
