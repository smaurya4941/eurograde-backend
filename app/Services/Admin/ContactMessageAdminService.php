<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\ContactMessage;
use Illuminate\Pagination\LengthAwarePaginator;

final class ContactMessageAdminService
{
    /**
     * @return LengthAwarePaginator<int, ContactMessage>
     */
    public function list(int $page, int $perPage, ?bool $handled): LengthAwarePaginator
    {
        return ContactMessage::query()
            ->when($handled === true, static fn ($q) => $q->whereNotNull('handled_at'))
            ->when($handled === false, static fn ($q) => $q->whereNull('handled_at'))
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    public function markHandled(ContactMessage $message, bool $handled): ContactMessage
    {
        $message->update(['handled_at' => $handled ? now() : null]);

        return $message;
    }

    public function delete(ContactMessage $message): void
    {
        $message->delete();
    }
}
