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
            'name' => 'Munavvarhushen Durvesh',
            'email' => 'munavvard222@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        User::factory()->create([
            'name' => 'Munavvarhushen Durvesh',
            'email' => 'munavvard2222@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'vip',
        ]);
        User::factory()->create([
            'name' => 'Munavvarhushen Durvesh',
            'email' => 'munavvard22322@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'normal',
        ]);

    }
}
