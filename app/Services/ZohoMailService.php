<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ZohoMailService
{
    private $apiUrl;
    private $clientId;
    private $clientSecret;
    private $refreshToken;
    private $accessToken;

    public function __construct()
    {
        $this->apiUrl = 'https://mail.zoho.com/api/';
        $this->clientId = config('services.zoho.client_id');
        $this->clientSecret = config('services.zoho.client_secret');
        $this->refreshToken = config('services.zoho.refresh_token');
    }

    /**
     * Get access token from Zoho using refresh token
     */
    private function getAccessToken()
    {
        try {
            $response = Http::asForm()->post('https://accounts.zoho.com/oauth/v2/token', [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                return $this->accessToken;
            }

            throw new Exception('Failed to get access token: ' . $response->body());
        } catch (Exception $e) {
            Log::error('Zoho Access Token Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send email using Zoho Mail API
     */
    public function sendEmail($toEmail, $toName, $subject, $htmlContent, $textContent = null)
    {
        try {
            // Get fresh access token
            $accessToken = $this->getAccessToken();

            // Prepare email data
            $emailData = [
                'fromAddress' => config('services.zoho.from_email'),
                'toAddress' => $toEmail,
                'subject' => $subject,
                'content' => $htmlContent,
                'contentType' => 'html'
            ];

            // Add text content if provided
            if ($textContent) {
                $emailData['textContent'] = $textContent;
            }

            // Send email via Zoho API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . 'accounts/self/messages', $emailData);

            if ($response->successful()) {
                Log::info('Email sent successfully via Zoho API', [
                    'to' => $toEmail,
                    'subject' => $subject
                ]);
                return true;
            }

            throw new Exception('Failed to send email: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Zoho Mail API Error: ' . $e->getMessage(), [
                'to' => $toEmail,
                'subject' => $subject
            ]);
            return false;
        }
    }

    /**
     * Send email verification
     */
    public function sendVerificationEmail($user, $verificationUrl)
    {
        $subject = 'Verify Your Email Address - ' . config('app.name');
        
        $htmlContent = view('emails.verify-email', [
            'user' => $user,
            'verificationUrl' => $verificationUrl
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            $subject,
            $htmlContent
        );
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($user, $resetUrl)
    {
        $subject = 'Reset Your Password - ' . config('app.name');
        
        $htmlContent = view('emails.password-reset', [
            'user' => $user,
            'resetUrl' => $resetUrl
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            $subject,
            $htmlContent
        );
    }

    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($user)
    {
        $subject = 'Welcome to ' . config('app.name') . '!';
        
        $htmlContent = view('emails.welcome', [
            'user' => $user
        ])->render();

        return $this->sendEmail(
            $user->email,
            $user->name,
            $subject,
            $htmlContent
        );
    }
}