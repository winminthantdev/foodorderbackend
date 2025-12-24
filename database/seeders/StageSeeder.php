<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Status;

class StageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stages = [
            "Panding",
            "Completed",
            "Processing",
            "Shipping",
            "Delivered",
            "Cancelled",
            "Refunded",
            "Failed"
        ];

        foreach ($stages as $name){
            Status::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }

}
