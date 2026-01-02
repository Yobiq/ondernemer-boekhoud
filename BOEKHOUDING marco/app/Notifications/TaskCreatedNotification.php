<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Task $task
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $typeDescription = match($this->task->type) {
            'missing_document' => 'Ontbrekend document',
            'unreadable' => 'Document onleesbaar',
            'clarification' => 'Verduidelijking nodig',
            default => 'Nieuwe taak',
        };
        
        return (new MailMessage)
            ->subject("Nieuwe taak: {$typeDescription}")
            ->greeting("Beste {$notifiable->name},")
            ->line("Er is een nieuwe taak voor u aangemaakt door uw boekhouder.")
            ->line("**Type:** {$typeDescription}")
            ->line("**Omschrijving:**")
            ->line($this->task->description)
            ->action('Bekijk taak', url("/admin/tasks/{$this->task->id}"))
            ->line('Log in op het systeem om de taak te bekijken en een antwoord te uploaden.')
            ->line('Met vriendelijke groet,')
            ->line('NL Accounting Core');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_type' => $this->task->type,
            'description' => $this->task->description,
            'document_id' => $this->task->document_id,
        ];
    }
}
