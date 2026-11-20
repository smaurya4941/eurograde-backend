<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Faq
 */
final class FaqResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->value,
            'question' => $this->question,
            'answer' => $this->answer,
            'tags' => $this->tags ?? [],
        ];
    }
}
