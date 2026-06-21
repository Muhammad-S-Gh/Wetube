<?php

namespace Database\Seeders;

// use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->withPersonalTeam()->create([
            'name' => 'Load Test',
            'email' => 'loadtest@example.com',
            'password' => bcrypt('1234567890'),
        ]);
    }
}
