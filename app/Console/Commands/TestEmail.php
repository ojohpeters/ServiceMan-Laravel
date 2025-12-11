<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing email configuration...');
        $this->info('Mail Driver: ' . config('mail.default'));
        $this->info('Mail Host: ' . config('mail.mailers.smtp.host'));
        $this->info('Mail Port: ' . config('mail.mailers.smtp.port'));
        $this->info('Mail Username: ' . (config('mail.mailers.smtp.username') ? 'Set' : 'Not Set'));
        $this->info('Mail Password: ' . (config('mail.mailers.smtp.password') ? 'Set' : 'Not Set'));
        $this->info('Mail From: ' . config('mail.from.address'));
        $this->info('Mail From Name: ' . config('mail.from.name'));
        
        $this->newLine();
        
        try {
            $this->info("Attempting to send test email to: {$email}");
            
            Mail::raw('This is a test email from ServiceMan. If you receive this, your email configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('ServiceMan - Test Email');
            });
            
            $this->info('✅ Email sent successfully!');
            $this->warn('Note: If using "log" driver, check storage/logs/laravel.log for the email content.');
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Email test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}

