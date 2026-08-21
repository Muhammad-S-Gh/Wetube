<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class K6TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = '1234567890';

        for ($i = 1; $i <= 100; $i++) {
            $email = "loadtest{$i}@example.com";

            // Create user only if not exists
            if (!User::where('email', $email)->exists()) {
                User::factory()
                    ->withPersonalTeam()
                    ->create([
                        'name' => "Load Test {$i}",
                        'email' => $email,
                        'password' => Hash::make($password),
                    ]);
            }
        }
        $this->command->info('100 test users created (or already existed) with password: ' . $password);
    }
}
