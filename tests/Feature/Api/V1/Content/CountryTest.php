<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Enums\FaqCategory;
use App\Models\Country;
use App\Models\CountryRequirement;
use App\Models\CountryStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CountryTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_countries_with_highlighted_stats(): void
    {
        $germany = Country::factory()->create([
            'name' => 'Germany', 'slug' => 'germany', 'sort_order' => 1, 'flag_path' => 'countries/flags/germany.svg',
        ]);
        Country::factory()->create(['name' => 'Italy', 'slug' => 'italy', 'sort_order' => 2]);
        Country::factory()->inactive()->create(['sort_order' => 0]);
        Country::factory()->create(['sort_order' => 0])->delete(); // soft-deleted

        CountryStat::factory()->for($germany)->highlighted()->create(['label' => 'Tuition', 'value' => '$1,500', 'sort_order' => 1]);
        CountryStat::factory()->for($germany)->create(['label' => 'Universities', 'value' => '400+']);
        CountryRequirement::factory()->for($germany)->count(3)->create();

        $this->getJson('/api/v1/countries')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'germany')
            ->assertJsonPath('data.0.flag_url', config('app.url').'/storage/countries/flags/germany.svg')
            ->assertJsonPath('data.0.highlights', [['label' => 'Tuition', 'value' => '$1,500']])
            ->assertJsonPath('data.0.requirements_count', 3)
            ->assertJsonPath('data.1.slug', 'italy')
            ->assertJsonPath('data.1.highlights', [])
            ->assertJsonStructure(['data' => [['id', 'slug', 'name', 'iso_code', 'flag_url', 'excerpt', 'highlights', 'requirements_count']]]);
    }

    public function test_shows_a_country_with_its_detail_content(): void
    {
        $country = Country::factory()->create([
            'name' => 'Germany', 'slug' => 'germany', 'faq_category' => FaqCategory::Germany, 'meta_title' => null,
        ]);
        CountryStat::factory()->for($country)->create(['label' => 'Universities', 'value' => '400+']);
        CountryRequirement::factory()->for($country)->create(['title' => 'Second', 'sort_order' => 2]);
        CountryRequirement::factory()->for($country)->create(['title' => 'First', 'sort_order' => 1]);
        $country->visas()->create(['name' => 'Student Visa', 'fee' => '€75', 'sort_order' => 1]);
        $country->livingCosts()->create(['label' => 'Rent', 'amount' => '€300 to €700', 'sort_order' => 1]);
        $country->cities()->create([
            'name' => 'Munich', 'description' => 'Engineering hub', 'image_path' => 'countries/cities/munich.webp', 'universities_count' => 10,
        ]);
        $country->reasons()->create(['title' => 'Low fees', 'description' => 'Tuition-free public universities', 'sort_order' => 1]);
        $country->update(['why_choose_intro' => 'Why intro', 'why_choose_image_path' => 'countries/why/germany.webp', 'faq_intro' => 'FAQ intro']);

        $this->getJson('/api/v1/countries/germany')
            ->assertOk()
            ->assertJsonPath('data.name', 'Germany')
            ->assertJsonPath('data.faq_category', 'germany')
            ->assertJsonPath('data.meta.title', 'Germany')
            ->assertJsonPath('data.requirements.0.title', 'First')
            ->assertJsonPath('data.requirements.1.title', 'Second')
            ->assertJsonPath('data.visas.0.fee', '€75')
            ->assertJsonPath('data.living_costs.0.label', 'Rent')
            ->assertJsonPath('data.cities.0.universities_count', 10)
            ->assertJsonPath('data.cities.0.image_url', config('app.url').'/storage/countries/cities/munich.webp')
            ->assertJsonPath('data.stats.0.value', '400+')
            ->assertJsonPath('data.cities.0.description', 'Engineering hub')
            ->assertJsonPath('data.why_choose.intro', 'Why intro')
            ->assertJsonPath('data.why_choose.image_url', config('app.url').'/storage/countries/why/germany.webp')
            ->assertJsonPath('data.why_choose.reasons.0.title', 'Low fees')
            ->assertJsonPath('data.faq_intro', 'FAQ intro');
    }

    public function test_unknown_or_inactive_country_returns_404(): void
    {
        Country::factory()->inactive()->create(['slug' => 'hidden']);

        $this->getJson('/api/v1/countries/hidden')->assertNotFound();
        $this->getJson('/api/v1/countries/atlantis')->assertNotFound();
    }

    public function test_cached_listing_is_refreshed_when_a_child_record_changes(): void
    {
        $country = Country::factory()->create();
        $stat = CountryStat::factory()->for($country)->highlighted()->create(['value' => 'Before']);
        $this->getJson('/api/v1/countries')->assertJsonPath('data.0.highlights.0.value', 'Before');

        $stat->update(['value' => 'After']);

        $this->getJson('/api/v1/countries')->assertJsonPath('data.0.highlights.0.value', 'After');
    }
}
