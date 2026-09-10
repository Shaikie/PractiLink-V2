<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
    public function __construct(private readonly Application $application) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'title' => 'Application status updated',
            'message' => 'Application '.$this->application->reference_number.' is now '.str_replace('_', ' ', $this->application->status).'.',
            'application_id' => $this->application->id,
            'url' => route('student.applications.show', $this->application),
        ]);
    }
}
