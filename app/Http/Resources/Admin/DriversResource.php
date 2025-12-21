<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriversResource extends JsonResource
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
            "name"=> $this->name,
            "phone_number"=> $this->phone_number,
            "status"=> [
                "id"=> $this->status->id ?? null,
                "name"=> $this->status->name ?? null,
            ],
            "latitude"=> $this->latitude,
            "longitude"=> $this->longitude,
            "rating"=> $this->rating,
            "created_at"=> $this->created_at->format("d m Y"),
            "updated_at"=> $this->updated_at->format("d m Y"),
        ];
    }
}
