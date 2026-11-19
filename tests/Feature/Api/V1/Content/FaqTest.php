<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Enums\FaqCategory;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_faqs_for_a_category_in_order(): void
    {
        Faq::factory()->category(FaqCategory::Immigration)->create(['question' => 'B?', 'sort_order' => 2]);
        Faq::factory()->category(FaqCategory::Immigration)->create(['question' => 'A?', 'sort_order' => 1, 'tags' => ['Visa']]);
        Faq::factory()->category(FaqCategory::Immigration)->inactive()->create();
        Faq::factory()->category(FaqCategory::Students)->create();

        $this->getJson('/api/v1/faqs?category=immigration')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.question', 'A?')
            ->assertJsonPath('data.0.category', 'immigration')
            ->assertJsonPath('data.0.tags', ['Visa'])
            ->assertJsonPath('data.1.tags', []);
    }

    public function test_category_is_required_and_must_be_known(): void
    {
        $this->getJson('/api/v1/faqs')->assertUnprocessable()->assertJsonValidationErrors('category');
        $this->getJson('/api/v1/faqs?category=nope')->assertUnprocessable()->assertJsonValidationErrors('category');
    }
}
