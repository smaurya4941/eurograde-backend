<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Admin;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin NewsletterSubscriber
 */
final class AdminNewsletterSubscriberResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'source_page' => $this->source_page,
            'unsubscribed_at' => $this->unsubscribed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
