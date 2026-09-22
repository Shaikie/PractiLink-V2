<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
        private readonly ?string $statusLabel = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Application status updated',
            'message' => $this->message(),
            'application_id' => $this->application->id,
            'url' => route('student.applications.show', $this->application),
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('PractiLink application status updated')
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line($this->message())
            ->action('View application', route('student.applications.show', $this->application))
            ->line('You can sign in to PractiLink to review the latest details and progress.');
    }

    private function message(): string
    {
        $status = $this->statusLabel
            ?? str_replace('_', ' ', $this->application->status);

        return 'Your application '.$this->application->reference_number.' is now '.$status.'.';
    }
}
