<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestEmail extends Command
{
    protected $signature = 'test:email {email}';
    protected $description = 'Send a test email';

    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            Mail::raw('This is a test email from ' . config('app.name'), function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email from ' . config('app.name'));
            });
            
            $this->info('✅ Test email sent successfully to: ' . $email);
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            Log::error('Email test failed', ['error' => $e->getMessage(), 'email' => $email]);
        }
    }
}