<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\ResetPasswordEmail;

class TestPasswordResetEmail extends Command
{
    protected $signature = 'email:test-password-reset {email}';
    protected $description = 'Test password reset email sending';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing password reset email...');
        $this->line('Email: ' . $email);
        $this->line('');
        
        // Find or create a test user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->warn('User not found. Creating temporary user for testing...');
            $user = new User();
            $user->email = $email;
            $user->first_name = 'Test';
            $user->last_name = 'User';
        }
        
        $token = \Str::random(64);
        $resetUrl = url('/reset-password/' . $token . '?email=' . $email);
        
        $this->info('Attempting to send password reset email...');
        $this->line('Reset URL: ' . $resetUrl);
        $this->line('');
        
        try {
            Mail::to($email)->send(new ResetPasswordEmail($user, $resetUrl));
            $this->info('✅ Password reset email sent successfully!');
            $this->line('');
            $this->line('Check your inbox (and spam folder) at: ' . $email);
        } catch (\Exception $e) {
            $this->error('❌ Failed to send password reset email!');
            $this->error('');
            $this->error('Error: ' . $e->getMessage());
            $this->error('');
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
            $this->error('');
            $this->error('Stack trace:');
            $this->line($e->getTraceAsString());
            
            \Log::error('Password reset email test failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return 1;
        }
        
        return 0;
    }
}

