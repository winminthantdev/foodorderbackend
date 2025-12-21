<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
     use HasFactory;
     protected $table = "drivers";
     protected $primaryKey = 'id';
    protected $fillable = [
        "name",
        "phone_number",
        "avatar",
        "status_id",
        "latitude",
        "longitude",
        "rating",
        "user_id",
    ] ;

    public function status(){
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function ratings(){
        return $this->morphMany(Rateable::class, 'rateable');
    }

    public function averageRating(){
        return $this->ratings()->avg('rating');
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
