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
        return [
            "id"=> $this->id,
            "name"=> $this->name,
            "slug"=> $this->slug,
            "image"=> $this->image,
            "description"=> $this->description,
            "price"=> $this->price,
            "rating"=> $this->rating,
            "subcatrgory"=>[
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
            "created_at"=> $this->created_at->format("d m Y"),
            "updated_at"=> $this->updated_at->format("d m Y"),
        ];
    }
}
