<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    public function __construct(private readonly string $token, private readonly string $accountType) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'account_type' => $this->accountType,
        ]);

        return (new MailMessage)
            ->subject('Reset your PractiLink password')
            ->greeting('Hello '.($notifiable->full_name ?? $notifiable->fullname ?? 'there').',')
            ->line('We received a request to reset your PractiLink password.')
            ->action('Reset password', $url)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no action is required.');
    }
}
