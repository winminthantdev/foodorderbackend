<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuAddonsResource extends JsonResource
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
            "addon"=> [
                "id"=> $this->addon->id,
                "name"=> $this->addon->name,
            ],
            "max_quantity"=> $this->max_quantity,
            "custom_price"=> $this->custom_price,
        ];
    }
}
