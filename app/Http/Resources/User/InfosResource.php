<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfosResource extends JsonResource
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
            "gender"=> $this->gender,
            "avatar"=> $this->avatar,
            "date_of_birth" => $this->date_of_birth,
            "loyalty_points" => $this->loyalty_points,
            "notification_enabled" => (bool) $this->notification_enabled,
            "last_active" => $this->last_active,
            "created_at" => $this->created_at->format("d m Y"),
            "updated_at" => $this->updated_at->format("d m Y"),
        ];
    }
}
