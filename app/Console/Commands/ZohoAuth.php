<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ZohoAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoho:auth {action=generate-url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Zoho authorization URL or exchange code for tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'generate-url':
                $this->generateAuthUrl();
                break;
            case 'get-token':
                $this->getTokenFromCode();
                break;
            default:
                $this->error('Invalid action. Use: generate-url or get-token');
        }
    }
    
    private function generateAuthUrl()
    {
        $clientId = config('services.zoho.client_id');
        
        if (!$clientId) {
            $this->error('ZOHO_CLIENT_ID not found in .env file');
            return;
        }
        
        // Using Self Client - no redirect URI needed
        $authUrl = 'https://accounts.zoho.com/oauth/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => 'ZohoMail.messages.CREATE,ZohoMail.accounts.READ',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ]);
        
        $this->info('🔗 Zoho Authorization URL (Self Client):');
        $this->line($authUrl);
        $this->info('');
        $this->info('📝 Steps:');
        $this->line('1. Copy the URL above and open it in your browser');
        $this->line('2. Login to your Zoho account and authorize the app');
        $this->line('3. You will see the authorization code in the browser');
        $this->line('4. Copy the code and run: php artisan zoho:auth get-token');
        $this->info('');
        $this->info('💡 Alternative: Create Self Client in Zoho Console');
        $this->line('   - Go to: https://api-console.zoho.com/');
        $this->line('   - Create "Self Client" instead of "Server-based Application"');
        $this->line('   - Self Client doesn\'t need redirect URI configuration');
    }
    
    private function getTokenFromCode()
    {
        $code = $this->ask('Enter the authorization code from Zoho:');
        
        if (!$code) {
            $this->error('Authorization code is required');
            return;
        }
        
        $clientId = config('services.zoho.client_id');
        $clientSecret = config('services.zoho.client_secret');
        
        try {
            // For Self Client - no redirect URI needed
            $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                $this->info('✅ Tokens generated successfully!');
                $this->info('');
                $this->info('📋 Add these to your .env file:');
                $this->line('ZOHO_REFRESH_TOKEN=' . $data['refresh_token']);
                $this->info('');
                $this->info('🔄 Access Token (for testing):');
                $this->line($data['access_token']);
                $this->info('');
                $this->info('⏰ Token expires in: ' . $data['expires_in'] . ' seconds');
            } else {
                $this->error('❌ Failed to get tokens: ' . $response->body());
                $this->info('');
                $this->info('💡 Common issues:');
                $this->line('1. Make sure you created a "Self Client" not "Server-based Application"');
                $this->line('2. Check if Client ID and Secret are correct');
                $this->line('3. Make sure the authorization code is fresh (expires quickly)');
            }
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}
