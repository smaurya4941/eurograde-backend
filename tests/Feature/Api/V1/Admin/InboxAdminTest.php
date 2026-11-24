<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InboxAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/v1/admin/contact-messages')
            ->assertForbidden();
    }

    public function test_admin_can_list_and_mark_a_message_handled(): void
    {
        $admin = User::factory()->admin()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($admin, 'web')
            ->getJson('/api/v1/admin/contact-messages')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($admin, 'web')
            ->patchJson("/api/v1/admin/contact-messages/{$message->id}", ['handled' => true])
            ->assertOk()
            ->assertJsonPath('data.handled_at', fn (?string $value) => $value !== null);

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id]);
    }

    public function test_admin_can_filter_unhandled_messages(): void
    {
        $admin = User::factory()->admin()->create();
        ContactMessage::factory()->create(['handled_at' => now()]);
        ContactMessage::factory()->create(['handled_at' => null]);

        $this->actingAs($admin, 'web')
            ->getJson('/api/v1/admin/contact-messages?handled=0')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_delete_a_contact_message(): void
    {
        $admin = User::factory()->admin()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/contact-messages/{$message->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }

    public function test_admin_can_list_and_delete_newsletter_subscribers(): void
    {
        $admin = User::factory()->admin()->create();
        $subscriber = NewsletterSubscriber::factory()->create();

        $this->actingAs($admin, 'web')
            ->getJson('/api/v1/admin/newsletter-subscribers')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/newsletter-subscribers/{$subscriber->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $subscriber->id]);
    }
}
