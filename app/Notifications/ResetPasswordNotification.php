<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;
    protected $email;

    /**
     * Create a new notification instance.
     */
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Use Laravel route instead of external frontend URL
        $resetUrl = route('password.reset', [
            'token' => $this->token, 
            'email' => $this->email
        ]);

        return (new MailMessage)
                    ->subject('Reset Password Notification - ' . config('app.name'))
                    ->greeting('Hello!')
                    ->line('You are receiving this email because we received a password reset request for your account.')
                    ->line('Click the button below to reset your password:')
                    ->action('Reset Password', $resetUrl)
                    ->line('This password reset link will expire in 60 minutes.')
                    ->line('If you did not request a password reset, no further action is required.')
                    ->line('For security reasons, please do not share this link with anyone.')
                    ->salutation('Best regards,<br>' . config('app.name') . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'token' => $this->token,
            'email' => $this->email,
        ];
    }
}