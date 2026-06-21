<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class LoadTestUserSeeder extends Seeder
{
  public function run(): void
  {
    for ($i = 1; $i <= 100; $i++) {
      User::query()->updateOrCreate(
        ['email' => "loadtest{$i}@example.com"],
        [
          'name' => "Load Test {$i}",
          'password' => bcrypt('1234567890'),
          'email_verified_at' => now(),
        ]
      );
    }
  }
}
