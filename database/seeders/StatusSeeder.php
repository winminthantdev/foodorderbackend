<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            'Active',
            'Inactive',
            "On",
            "Off",
            'Online',
            'Offline',
            'Public',
            'Private',
            'Friend Only',
            'Member Only',
            'Only Me',
            'Enable',
            'Disable',
            'Ban',
            'Unban',
            'Block',
            'Unblock',
            'Terminate',
        ];

        foreach ($statuses as $name){
            Status::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }

}
