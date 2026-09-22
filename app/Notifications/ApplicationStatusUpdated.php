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

    public function __construct(private readonly Application $application) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Application status updated',
            'message' => 'Application '.$this->application->reference_number.' is now '.str_replace('_', ' ', $this->application->status).'.',
            'application_id' => $this->application->id,
            'url' => route('student.applications.show', ['application' => $this->application->id]),
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = str_replace('_', ' ', $this->application->status);

        return (new MailMessage)
            ->subject('PractiLink application status updated')
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('Your application '.$this->application->reference_number.' is now '.$status.'.')
            ->action('View application', route('student.applications.show', ['application' => $this->application->id]))
            ->line('You can sign in to PractiLink to review the latest details and progress.');
    }
}
