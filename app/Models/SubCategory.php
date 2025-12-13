<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
     use HasFactory;

    protected $table = 'subcategories';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'status_id'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function status(){
        return $this->belongsTo(Status::class);
    }
}
