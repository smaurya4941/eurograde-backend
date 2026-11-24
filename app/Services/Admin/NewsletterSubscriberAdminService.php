<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\NewsletterSubscriber;
use Illuminate\Pagination\LengthAwarePaginator;

final class NewsletterSubscriberAdminService
{
    /**
     * @return LengthAwarePaginator<int, NewsletterSubscriber>
     */
    public function list(int $page, int $perPage, ?bool $subscribed): LengthAwarePaginator
    {
        return NewsletterSubscriber::query()
            ->when($subscribed === true, static fn ($q) => $q->whereNull('unsubscribed_at'))
            ->when($subscribed === false, static fn ($q) => $q->whereNotNull('unsubscribed_at'))
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    public function delete(NewsletterSubscriber $subscriber): void
    {
        $subscriber->delete();
    }
}
