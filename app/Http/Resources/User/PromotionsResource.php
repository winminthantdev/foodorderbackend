<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"=> $this->id,
            "title"=> $this->title,
            "code"=> $this->code,
            "description"=> $this->description,
            "type"=> $this->type,
            "value"=> $this->value,
            "max_discount"=> $this->max_discount,
            "min_order_amount"=> $this->min_order_amount,
            "start_date"=> $this->start_date,
            "end_date"=> $this->end_date,
            "status"=> [
                'id' => $this->status->id ?? null,
                'name' => $this->status->name ?? null,
            ],
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at
        ];
    }
}
