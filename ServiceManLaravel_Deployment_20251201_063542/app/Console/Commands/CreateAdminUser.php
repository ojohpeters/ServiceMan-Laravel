<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create admin user with email admin@serviceman.com and password password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Create admin user
            $admin = User::firstOrCreate(
                ['email' => 'admin@serviceman.com'],
                [
                    'username' => 'admin',
                    'password' => bcrypt('password'),
                    'user_type' => 'ADMIN',
                    'first_name' => 'Admin',
                    'last_name' => 'User',
                    'is_email_verified' => true,
                    'email_verified_at' => now()
                ]
            );

            $this->info('✅ Admin user created successfully!');
            $this->line('📧 Email: admin@serviceman.com');
            $this->line('🔑 Password: password');
            $this->line('👤 Type: ADMIN');
            
        } catch (\Exception $e) {
            $this->error('❌ Error creating admin user: ' . $e->getMessage());
        }
    }
}
