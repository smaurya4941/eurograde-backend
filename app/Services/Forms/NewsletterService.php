<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Models\NewsletterSubscriber;

final class NewsletterService
{
    /**
     * Subscribe an email address. Idempotent: an existing subscriber is
     * reactivated instead of producing a duplicate or an error.
     *
     * @return array{subscriber: NewsletterSubscriber, created: bool}
     */
    public function subscribe(string $email, ?string $sourcePage, ?string $ipAddress): array
    {
        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $email]);
        $created = ! $subscriber->exists;

        $subscriber->fill([
            'source_page' => $created ? $sourcePage : $subscriber->source_page,
            'ip_address' => $created ? $ipAddress : $subscriber->ip_address,
            'unsubscribed_at' => null,
        ])->save();

        return ['subscriber' => $subscriber, 'created' => $created];
    }
}
