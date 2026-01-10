<?php

namespace App\Http\Resources\Public;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $promotion = $this->activePromotion();

        $discountPercent = $promotion ? ($promotion->pivot->custom_discount_value ?? $promotion->discount_percent) : null;

        $finalPrice = $promotion ? $this->price - ($this->price * $discountPercent / 100) : $this->price;

        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "slug"=> $this->slug,
            "image"=> $this->image,
            "description"=> $this->description,
            "price"=> $this->price,
            'final_price' => round($finalPrice),
            "rating"=> $this->rating,
            "subcategory"=>[
                "id"=> $this->subcategory->id,
                "name"=> $this->subcategory->name,
                "slug"=> $this->subcategory->slug,
            ],
            "category"=>[
                "id"=> $this->category->id,
                "name"=> $this->category->name,
                "slug"=> $this->category->slug,
            ],
            "status"=> [
                "id"=> $this->status->id,
                "name"=> $this->status->name,
            ],
            "promotion" => $promotion ? [
                'id' => $promotion -> id,
                'isActive' => true,
                'title' => $promotion->title,
                "discountPercent" => $discountPercent,
                "max_discount" => $promotion->max_discount,
                "min_order_amount" => $promotion->min_order_amount,
            ]: null,
            "created_at"=> $this->created_at->format("d m Y"),
            "updated_at"=> $this->updated_at->format("d m Y"),
        ];
    }
}
