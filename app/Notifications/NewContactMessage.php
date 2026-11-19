<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Emails the office when someone submits the website contact form. */
final class NewContactMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly ContactMessage $contactMessage)
    {
        $this->afterCommit();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $m = $this->contactMessage;

        return (new MailMessage)
            ->subject("New website enquiry from {$m->full_name}")
            ->replyTo($m->email, $m->full_name)
            ->greeting('New contact form enquiry')
            ->line("**Name:** {$m->full_name}")
            ->line("**Email:** {$m->email}")
            ->line("**Phone:** {$m->phone}")
            ->line('**Page:** '.($m->source_page ?? 'unknown'))
            ->line('**Message:**')
            ->line($m->message);
    }
}
