<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Support\Facades\{Validator, Hash};
use Illuminate\Console\Command;
use App\Enums\UserRole;
use App\Models\User;

final class CreateAdminUser extends Command
{
    protected $signature = 'admin:create 
                            {--email= : Admin email address}
                            {--password= : Admin password}
                            {--name= : Admin name}';

    protected $description = 'Create an admin user';

    public function handle(): int
    {
        $email = $this->option('email') ?? $this->ask('Enter admin email');
        $name = $this->option('name') ?? $this->ask('Enter admin name');
        $password = $this->option('password') ?? $this->secret('Enter admin password');
        
        // Validate input
        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return Command::FAILURE;
        }

        try {
            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => UserRole::Admin->value,
            ]);

            $this->info("Admin user created successfully!");
            $this->table(
                ['ID', 'Name', 'Email', 'Admin'],
                [[$admin->id, $admin->name, $admin->email, $admin->role->value]]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create admin user: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}