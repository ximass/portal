<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'mateus schmitz',
            'email' => 'mateus.schmitz7@hotmail.com',
            'password' => bcrypt('123456'),
            'admin' => true
        ]);
    }
}
