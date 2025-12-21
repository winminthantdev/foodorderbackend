<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInfosResource extends JsonResource
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
            "user_id"=> $this->user_id,
            "gender"=> $this->gender,
            "avatar"=> $this->avatar,
            "date_of_birth"=> $this->date_of_birth,
            "loyalty_points"=> $this->loyalty_points,
            "notification_enabled"=> $this->notification_enabled,
            "last_active"=> $this->last_active,
            "is_blocked"=> $this->is_blocked,
        ];
    }
}
