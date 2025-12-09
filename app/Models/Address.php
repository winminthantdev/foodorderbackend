<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
     use HasFactory;
     protected $table = "addresses";
     protected $primaryKey = 'id';
    protected $fillable = [
        "label",
        "address_line1",
        "address_line2",
        "city",
        "latitude",
        "longitude",
        "is_default",
        "user_id",
    ] ;

    public function user(){
        return $this->belongsTo(User::class);
    }
}
