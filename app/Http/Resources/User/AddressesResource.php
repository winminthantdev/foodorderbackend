<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressesResource extends JsonResource
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
            "label" => $this->label,
            "address_line1" => $this->address_line1,
            "address_line2" => $this->address_line2,
            "city" => $this->city,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "is_default" => (bool)$this->is_default,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
