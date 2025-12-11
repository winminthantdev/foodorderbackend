<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
     use HasFactory;

    protected $table = 'menus';
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
        'status_id'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function subcategory(){
        return $this->belongsTo(Subcategory::class);
    }

    public function ratings(){
        return $this->morphMany(Rateable::class, 'rateable');
    }
    public function status(){
        return $this->belongsTo(Status::class);
    }
}
