<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantsResource extends JsonResource
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
            "menu"=>[
                "id"=> $this->menu->id,
                "name" => $this->menu->name
            ],
            "name"=> $this->variant->name,
            "extra_price"=> $this->extra_price,
            "status" => [
                "id"=> $this->status->id,
                "name"=> $this->status->name
            ]
        ];
    }
}
