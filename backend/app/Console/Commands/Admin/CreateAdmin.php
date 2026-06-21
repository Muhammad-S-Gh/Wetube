<?php

namespace App\Console\Commands\Admin;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email} {--name=} {--password=}';

    protected $description = 'Create a new admin user';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->option('name') ?? explode('@', $email)[0];
        $password = $this->option('password') ?? bin2hex(random_bytes(6));

        if (User::where('email', $email)->exists()) {
            $this->error("A user with {$email} already exists. Admin accounts must be created with a new email address.");

            return Command::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info("Created admin user {$email}.");

        $this->info('Admin credentials:');
        $this->line('Email: ' . $user->email);
        $this->line('Password: ' . $password);

        return Command::SUCCESS;
    }
}
