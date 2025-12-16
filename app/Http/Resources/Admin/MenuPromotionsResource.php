<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuPromotionsResource extends JsonResource
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
            "menu"=> [
                "id"=> $this->menu->id,
                "name"=> $this->menu->name,
                "slug"=> $this->menu->slug,
            ],
            "promotion"=> [
                "id"=> $this->promotion->id,
                "code"=> $this->promotion->code,
                "description"=> $this->promotion->description,
            ],
            "custom_discount_value"=> $this->custom_discount_value,
        ];
    }
}
