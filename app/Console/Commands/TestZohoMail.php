<?php

namespace App\Console\Commands;

use App\Services\ZohoMailService;
use Illuminate\Console\Command;

class TestZohoMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:zoho-mail {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Zoho Mail API by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle(ZohoMailService $zohoMailService)
    {
        $email = $this->argument('email');
        
        $this->info("Sending test email to: {$email}");
        
        $result = $zohoMailService->sendEmail(
            $email,
            'Test User',
            'Test Email from ' . config('app.name'),
            '<h1>Test Email</h1><p>This is a test email sent via Zoho Mail API!</p><p>Date: ' . now() . '</p>',
            'Test Email - This is a test email sent via Zoho Mail API! Date: ' . now()
        );
        
        if ($result) {
            $this->info('✅ Email sent successfully!');
        } else {
            $this->error('❌ Failed to send email. Check logs for details.');
        }
    }
}
