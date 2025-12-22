<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdersResource extends JsonResource
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
            "user"=> [
                "id"=> $this->user->id,
                "name"=> $this->user->name,
                "email"=> $this->user->email,
            ],
            "ordertype"=> [
                "id"=> $this->ordertype->id,
                "name"=> $this->ordertype->name,
            ],
            "paymenttype"=> [
                "id"=> $this->paymenttype->id,
                "name"=> $this->paymenttype->name,
            ],
            "driver"=> $this->driver ? [
                "id"=> $this->driver->id,
                "name"=> $this->driver->name,
                "phone_number"=> $this->driver->phone_number,
            ] : null,
            "stage"=> [
                "id"=> $this->stage->id,
                "name"=> $this->stage->name,
            ],
            "address_id"=> [
                "id"=> $this->address->id,
                "address_line1"=> $this->address->address_line1,
                "address_line2"=> $this->address->address_line2,
                "city"=> $this->address->city,
                "latitude"=> $this->address->latitude,
                "longitude"=> $this->address->longitude,
            ],
            "subtotal"=> $this->subtotal,
            "discount"=> $this->discount,
            "delivery_fee"=> $this->delivery_fee,
            "service_fee"=> $this->service_fee,
            "total"=> $this->total,
            "transaction_id"=> $this->transaction_id,
            "is_paid"=> (bool) $this->is_paid,
            "order_note"=> $this->order_note,
            "scheduled_at"=> $this->scheduled_at,
            "created_at"=> $this->created_at->format("d m Y"),
            "updated_at"=> $this->updated_at->format("d m Y"),
        ];
    }
}
