<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PostComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Emails the office when a reader comments on a blog post (so it can be moderated). */
final class NewPostComment extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly PostComment $comment)
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
        $c = $this->comment->loadMissing('post');
        $status = $c->isApproved() ? 'published' : 'awaiting moderation';

        return (new MailMessage)
            ->subject("New comment on \"{$c->post->title}\"")
            ->replyTo($c->author_email, $c->author_name)
            ->greeting('New blog comment')
            ->line("**Article:** {$c->post->title}")
            ->line("**From:** {$c->author_name} ({$c->author_email})")
            ->line('**Website:** '.($c->author_website ?? '—'))
            ->line("**Status:** {$status}")
            ->line('**Comment:**')
            ->line($c->body);
    }
}
