<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Forms;

use App\Notifications\NewContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'first_name' => ' Jane ',
            'last_name' => 'Doe',
            'email' => 'Jane@Example.com',
            'phone' => '+1 (800) 456-7890',
            'message' => 'I would like advice on a Canadian study permit.',
            'source_page' => 'about',
        ];
    }

    public function test_message_is_stored_and_office_is_notified(): void
    {
        Notification::fake();
        config(['site.contact.notify_email' => 'office@eurograde.test']);

        $this->postJson('/api/v1/contact-messages', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('contact_messages', [
            'first_name' => 'Jane',
            'email' => 'jane@example.com',
            'source_page' => 'about',
        ]);

        Notification::assertSentTo(
            new AnonymousNotifiable,
            NewContactMessage::class,
            static fn (NewContactMessage $n, array $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'office@eurograde.test'
                && $n->contactMessage->email === 'jane@example.com',
        );
    }

    public function test_no_email_is_sent_when_no_recipient_is_configured(): void
    {
        Notification::fake();
        config(['site.contact.notify_email' => null]);

        $this->postJson('/api/v1/contact-messages', $this->validPayload())->assertCreated();

        Notification::assertNothingSent();
        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_input_is_validated(): void
    {
        $this->postJson('/api/v1/contact-messages', ['email' => 'not-an-email', 'phone' => 'abc'])
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'phone', 'message']);
    }

    public function test_honeypot_submissions_look_successful_but_are_discarded(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/contact-messages', [...$this->validPayload(), 'website' => 'http://spam.example'])
            ->assertCreated();

        $this->assertDatabaseCount('contact_messages', 0);
        Notification::assertNothingSent();
    }

    public function test_submissions_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/contact-messages', $this->validPayload())->assertCreated();
        }

        $this->postJson('/api/v1/contact-messages', $this->validPayload())
            ->assertTooManyRequests()
            ->assertJsonPath('success', false);
    }
}
