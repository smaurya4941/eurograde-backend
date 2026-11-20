<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Models\SiteSetting;
use App\Models\SiteStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_public_settings_grouped_with_stats(): void
    {
        SiteSetting::query()->create(['key' => 'contact.phone', 'value' => '+44 20 4154 2541']);
        SiteSetting::query()->create(['key' => 'contact.email', 'value' => 'contact@example.com']);
        SiteSetting::query()->create(['key' => 'contact.phone_alt', 'value' => '+44 20 4154 2542']);
        SiteSetting::query()->create(['key' => 'contact.hours_weekend', 'value' => 'Saturday: 9:00 AM – 4:00 PM']);
        SiteSetting::query()->create(['key' => 'social.youtube', 'value' => 'https://youtube.com/x']);
        SiteSetting::query()->create(['key' => 'social.facebook', 'value' => 'https://facebook.com/x']);
        SiteSetting::query()->create(['key' => 'social.instagram', 'value' => '']);
        SiteSetting::query()->create(['key' => 'contact.secret', 'value' => 'hidden', 'is_public' => false]);
        SiteStat::factory()->create(['key' => 'students', 'label' => 'Students', 'value' => 300, 'sort_order' => 2]);
        SiteStat::factory()->create(['key' => 'counselors', 'label' => 'Counselors', 'value' => 80, 'sort_order' => 1]);
        SiteStat::factory()->create(['key' => 'old', 'is_active' => false]);

        $this->getJson('/api/v1/site-settings')
            ->assertOk()
            ->assertJsonPath('data.contact.phone', '+44 20 4154 2541')
            ->assertJsonPath('data.contact.phone_href', 'tel:+442041542541')
            ->assertJsonPath('data.contact.email', 'contact@example.com')
            ->assertJsonPath('data.contact.phone_alt', '+44 20 4154 2542')
            ->assertJsonPath('data.contact.phone_alt_href', 'tel:+442041542542')
            ->assertJsonPath('data.contact.hours_weekend', 'Saturday: 9:00 AM – 4:00 PM')
            ->assertJsonPath('data.contact.address', null)
            ->assertJsonMissingPath('data.contact.secret')
            // Display order is fixed (facebook before youtube); empty values are skipped.
            ->assertJsonPath('data.socials', [
                ['network' => 'facebook', 'url' => 'https://facebook.com/x'],
                ['network' => 'youtube', 'url' => 'https://youtube.com/x'],
            ])
            ->assertJsonCount(2, 'data.stats')
            ->assertJsonPath('data.stats.0', ['key' => 'counselors', 'label' => 'Counselors', 'value' => 80, 'suffix' => '+']);
    }

    public function test_settings_cache_is_refreshed_on_change(): void
    {
        $phone = SiteSetting::query()->create(['key' => 'contact.phone', 'value' => '111']);
        $this->getJson('/api/v1/site-settings')->assertJsonPath('data.contact.phone', '111');

        $phone->update(['value' => '222']);

        $this->getJson('/api/v1/site-settings')->assertJsonPath('data.contact.phone', '222');
    }
}
