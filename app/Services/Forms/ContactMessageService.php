<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Models\ContactMessage;
use App\Notifications\NewContactMessage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

final class ContactMessageService
{
    /**
     * Store an enquiry and (if configured) email the office about it.
     *
     * @param  array{first_name: string, last_name: string, email: string, phone: string, message: string, source_page: string|null}  $data
     */
    public function submit(array $data, ?string $ipAddress, ?string $userAgent): ContactMessage
    {
        $message = ContactMessage::query()->create([
            ...$data,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? Str::limit($userAgent, 500, '') : null,
        ]);

        $recipient = config('site.contact.notify_email');

        if (is_string($recipient) && $recipient !== '') {
            Notification::route('mail', $recipient)->notify(new NewContactMessage($message));
        }

        return $message;
    }
}
