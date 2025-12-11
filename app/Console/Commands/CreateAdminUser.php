<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {email} {--password=} {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user with the specified email (no dummy users)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->option('password');
        $name = $this->option('name') ?? 'Admin';

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email'
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('email')) {
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    $this->warn("⚠️  User with email {$email} already exists!");
                    if ($this->confirm("Do you want to update this user to be an admin?")) {
                        $existingUser->update([
                            'user_type' => 'ADMIN',
                            'is_approved' => true,
                            'is_email_verified' => true,
                            'email_verified_at' => now(),
                        ]);
                        $this->info("✅ User {$email} has been updated to ADMIN role!");
                        return 0;
                    }
                    return 1;
                }
            }
            $this->error("❌ Invalid email format!");
            return 1;
        }

        // Prompt for password if not provided
        if (!$password) {
            $password = $this->secret('Enter password for admin user (min 8 characters):');
            if (strlen($password) < 8) {
                $this->error("❌ Password must be at least 8 characters long!");
                return 1;
            }
            $confirmPassword = $this->secret('Confirm password:');
            if ($password !== $confirmPassword) {
                $this->error("❌ Passwords do not match!");
                return 1;
            }
        }

        // Extract first and last name
        $nameParts = explode(' ', $name, 2);
        $firstName = $nameParts[0] ?? 'Admin';
        $lastName = $nameParts[1] ?? 'User';
        
        // Generate username from email
        $username = explode('@', $email)[0];
        
        // Check if username exists and make it unique if needed
        $originalUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        // Create admin user
        $admin = User::create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'user_type' => 'ADMIN',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'is_approved' => true,
        ]);

        $this->info("✅ Admin user created successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Email', $admin->email],
                ['Username', $admin->username],
                ['Name', $admin->full_name],
                ['User Type', $admin->user_type],
                ['Email Verified', $admin->is_email_verified ? 'Yes' : 'No'],
                ['Approved', $admin->is_approved ? 'Yes' : 'No'],
            ]
        );

        return 0;
    }
}
