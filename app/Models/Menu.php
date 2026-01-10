<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';
    protected $casts = [
        'image' => 'array',
    ];


    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'price',
        'rating',
        'subcategory_id',
        'category_id',
        'status_id',
    ];

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'menu_promotions')
            ->withPivot('custom_discount_value')
            ->withTimestamps();
    }


    public function activePromotion()
    {
        return $this->promotions()
            ->where('status_id', 1)
            ->where(function ($q) {
                $q->whereNull('start_date')
                ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now());
            })
            ->first();
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'menu_addons')
            ->withPivot(['max_quantity', 'custom_price'])
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(SubCategory::class);
    }


    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function ratings()
    {
        return $this->morphMany(Rateable::class, 'rateable');
    }

    public function getImageAttribute($value)
    {
        return $value
            ? url('/' . $value)
            : null;
    }
}
