<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentsResource extends JsonResource
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
            "amount"=> $this->amount,
            "order"=> [
                "id"=> $this->order->id,
                "total"=> $this->order->total,
            ],
            "status"=> [
                "id"=> $this->status->id,
                "name"=> $this->status->name,
            ],
            "payment_method"=> [
                "id"=> $this->paymentType->id,
                "name"=> $this->paymentType->name,
            ],
            "transaction_id"=> $this->transaction_id,
            "created_at"=> $this->created_at->format("d m Y"),
            "updated_at"=> $this->updated_at->format("d m Y"),
        ];
    }
}
