<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Forms;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_email_is_subscribed(): void
    {
        $this->postJson('/api/v1/newsletter-subscriptions', ['email' => ' Reader@Example.com ', 'source_page' => 'footer'])
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'reader@example.com', 'source_page' => 'footer']);
    }

    public function test_subscribing_twice_is_idempotent(): void
    {
        NewsletterSubscriber::factory()->create(['email' => 'reader@example.com']);

        $this->postJson('/api/v1/newsletter-subscriptions', ['email' => 'reader@example.com'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_unsubscribed_reader_is_reactivated(): void
    {
        NewsletterSubscriber::factory()->unsubscribed()->create(['email' => 'reader@example.com']);

        $this->postJson('/api/v1/newsletter-subscriptions', ['email' => 'reader@example.com'])->assertOk();

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'reader@example.com', 'unsubscribed_at' => null]);
    }

    public function test_email_is_validated(): void
    {
        $this->postJson('/api/v1/newsletter-subscriptions', ['email' => 'nope'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }
}
