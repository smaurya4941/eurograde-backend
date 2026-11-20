<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Enums\FaqCategory;
use App\Models\Country;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_services_in_display_order(): void
    {
        $germany = Country::factory()->create(['slug' => 'germany', 'name' => 'Germany']);
        Service::factory()->create(['title' => 'Second', 'sort_order' => 2]);
        Service::factory()->create([
            'title' => 'First', 'sort_order' => 1, 'country_id' => $germany->id, 'icon_path' => 'services/icons/first.svg',
        ]);
        Service::factory()->inactive()->create(['sort_order' => 0]);
        Service::factory()->create(['sort_order' => 0])->delete();

        $this->getJson('/api/v1/services')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'First')
            ->assertJsonPath('data.0.country', ['slug' => 'germany', 'name' => 'Germany'])
            ->assertJsonPath('data.0.icon_url', config('app.url').'/storage/services/icons/first.svg')
            ->assertJsonPath('data.1.country', null)
            ->assertJsonStructure(['data' => [['id', 'slug', 'title', 'excerpt', 'icon_url', 'image_url', 'country']]]);
    }

    public function test_shows_a_service_with_its_detail_content(): void
    {
        $italy = Country::factory()->create(['slug' => 'italy', 'name' => 'Italy']);
        $service = Service::factory()->create([
            'slug' => 'study-in-italy',
            'title' => 'Study in Italy',
            'excerpt' => 'Affordable degrees',
            'image_path' => 'services/study-in-italy.webp',
            'country_id' => $italy->id,
            'overview' => "First paragraph.\n\nSecond paragraph.",
            'offer_title' => 'End-to-End Admission Support',
            'offer_highlight' => 'Admission Support',
            'offer_intro' => 'Offer intro',
            'why_title' => 'Why Choose Us',
            'why_highlight' => 'Us',
            'why_intro' => 'Why intro',
            'why_outro' => 'Why outro',
            'faq_category' => FaqCategory::Italy,
            'faq_intro' => 'FAQ intro',
            'meta_title' => null,
            'meta_description' => null,
        ]);
        $service->features()->create(['description' => 'Second', 'sort_order' => 2]);
        $service->features()->create(['description' => 'First', 'sort_order' => 1]);
        $service->images()->create(['image_path' => 'services/gallery/a.webp', 'alt' => 'Photo A', 'sort_order' => 1]);
        $service->metrics()->create(['label' => 'Visa Approval Rate', 'value' => 97, 'sort_order' => 1]);

        $this->getJson('/api/v1/services/study-in-italy')
            ->assertOk()
            ->assertJsonPath('data.title', 'Study in Italy')
            ->assertJsonPath('data.image_url', config('app.url').'/storage/services/study-in-italy.webp')
            ->assertJsonPath('data.overview', "First paragraph.\n\nSecond paragraph.")
            ->assertJsonPath('data.offer.title', 'End-to-End Admission Support')
            ->assertJsonPath('data.offer.highlight', 'Admission Support')
            ->assertJsonPath('data.offer.features', ['First', 'Second'])
            ->assertJsonPath('data.offer.images', [['url' => config('app.url').'/storage/services/gallery/a.webp', 'alt' => 'Photo A']])
            ->assertJsonPath('data.why.metrics', [['label' => 'Visa Approval Rate', 'value' => 97]])
            ->assertJsonPath('data.why.outro', 'Why outro')
            ->assertJsonPath('data.faq_category', 'italy')
            ->assertJsonPath('data.country', ['slug' => 'italy', 'name' => 'Italy'])
            ->assertJsonPath('data.meta', ['title' => 'Study in Italy', 'description' => 'Affordable degrees']);
    }

    public function test_detail_returns_404_for_unknown_inactive_or_deleted_services(): void
    {
        Service::factory()->inactive()->create(['slug' => 'hidden']);
        Service::factory()->create(['slug' => 'removed'])->delete();

        $this->getJson('/api/v1/services/unknown')->assertNotFound();
        $this->getJson('/api/v1/services/hidden')->assertNotFound();
        $this->getJson('/api/v1/services/removed')->assertNotFound();
    }

    public function test_detail_cache_is_flushed_when_child_content_changes(): void
    {
        $service = Service::factory()->create(['slug' => 'ielts-preparation']);
        $this->getJson('/api/v1/services/ielts-preparation')->assertJsonPath('data.why.metrics', []);

        $service->metrics()->create(['label' => 'Target band reached', 'value' => 90, 'sort_order' => 1]);

        $this->getJson('/api/v1/services/ielts-preparation')
            ->assertJsonPath('data.why.metrics', [['label' => 'Target band reached', 'value' => 90]]);
    }

    public function test_deleting_a_service_removes_its_detail_rows(): void
    {
        $service = Service::factory()->create();
        $service->features()->create(['description' => 'Item', 'sort_order' => 1]);
        $service->images()->create(['image_path' => 'services/gallery/a.webp', 'alt' => 'A', 'sort_order' => 1]);
        $service->metrics()->create(['label' => 'Rate', 'value' => 50, 'sort_order' => 1]);

        $service->forceDelete();

        $this->assertDatabaseCount('service_features', 0);
        $this->assertDatabaseCount('service_images', 0);
        $this->assertDatabaseCount('service_metrics', 0);
    }

    public function test_body_html_is_sanitised_on_save(): void
    {
        $service = Service::factory()->create(['body' => '<p>Hi</p><script>alert(1)</script>']);

        $this->assertStringNotContainsString('<script', (string) $service->fresh()?->body);
    }
}
