<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Admin::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name'=> 'Admin',
                'password' => Hash::make('Password123')
            ],
        );

        $this->call([
            StatusSeeder::class,
            StageSeeder::class,
        ]);
    }
}
