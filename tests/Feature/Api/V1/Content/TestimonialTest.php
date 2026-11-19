<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Enums\TestimonialCategory;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_testimonials_in_display_order(): void
    {
        Testimonial::factory()->create(['name' => 'Second', 'sort_order' => 2]);
        Testimonial::factory()->create([
            'name' => 'First',
            'sort_order' => 1,
            'avatar_path' => 'testimonials/first.webp',
            'highlights' => ['Student Visa'],
        ]);
        Testimonial::factory()->inactive()->create(['name' => 'Hidden', 'sort_order' => 0]);

        $this->getJson('/api/v1/testimonials')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'First')
            ->assertJsonPath('data.1.name', 'Second')
            ->assertJsonPath('data.0.avatar_url', config('app.url').'/storage/testimonials/first.webp')
            ->assertJsonPath('data.0.highlights', ['Student Visa'])
            ->assertJsonStructure(['data' => [[
                'id', 'slug', 'name', 'avatar_url', 'category', 'program', 'origin',
                'destination', 'achievement', 'quote', 'rating', 'highlights', 'is_featured',
            ]]]);
    }

    public function test_filters_by_one_or_more_categories(): void
    {
        Testimonial::factory()->category(TestimonialCategory::German)->create(['name' => 'German']);
        Testimonial::factory()->category(TestimonialCategory::Italian)->create(['name' => 'Italian']);
        Testimonial::factory()->category(TestimonialCategory::Ielts)->create(['name' => 'Ielts']);

        $this->getJson('/api/v1/testimonials?category[]=ielts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Ielts');

        $this->getJson('/api/v1/testimonials?category[]=german&category[]=italian')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_featured_filter_and_limit(): void
    {
        Testimonial::factory()->featured()->count(3)->create();
        Testimonial::factory()->count(2)->create();

        $this->getJson('/api/v1/testimonials?featured=1')->assertOk()->assertJsonCount(3, 'data');
        $this->getJson('/api/v1/testimonials?featured=1&limit=2')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/testimonials')->assertOk()->assertJsonCount(5, 'data');
    }

    public function test_query_parameters_are_validated(): void
    {
        $this->getJson('/api/v1/testimonials?category[]=cooking&limit=500&featured=maybe')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category.0', 'limit', 'featured']);
    }

    public function test_cached_listing_is_refreshed_when_content_changes(): void
    {
        $testimonial = Testimonial::factory()->create(['name' => 'Before']);
        $this->getJson('/api/v1/testimonials')->assertJsonPath('data.0.name', 'Before');

        $testimonial->update(['name' => 'After']);

        $this->getJson('/api/v1/testimonials')->assertJsonPath('data.0.name', 'After');
    }
}
