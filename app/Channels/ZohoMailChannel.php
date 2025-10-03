<?php

namespace App\Channels;

use App\Services\ZohoMailService;
use Illuminate\Notifications\Notification;

class ZohoMailChannel
{
    protected $zohoMailService;

    public function __construct(ZohoMailService $zohoMailService)
    {
        $this->zohoMailService = $zohoMailService;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toZohoMail')) {
            return;
        }

        $data = $notification->toZohoMail($notifiable);

        return $this->zohoMailService->sendEmail(
            $data['email'],
            $data['name'],
            $data['subject'],
            $data['content'],
            $data['text_content'] ?? null
        );
    }
}