<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TestimonialAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/v1/admin/testimonials')
            ->assertForbidden();
    }

    public function test_admin_can_create_a_testimonial(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/testimonials', [
                'name' => 'John Student',
                'category' => 'admissions',
                'program' => 'Master’s Student',
                'quote' => 'EuroRank made this easy.',
                'rating' => 5,
            ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'john-student');

        $this->assertDatabaseHas('testimonials', ['name' => 'John Student']);
    }

    public function test_creating_a_testimonial_validates_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/testimonials', [
                'name' => 'John Student',
                'category' => 'not-a-real-category',
                'program' => 'Master’s Student',
                'quote' => 'EuroRank made this easy.',
                'rating' => 5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category']);
    }

    public function test_admin_can_update_a_testimonial(): void
    {
        $admin = User::factory()->admin()->create();
        $testimonial = Testimonial::factory()->create();

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/testimonials/{$testimonial->id}", [
                'name' => $testimonial->name,
                'slug' => $testimonial->slug,
                'category' => $testimonial->category->value,
                'program' => $testimonial->program,
                'quote' => 'Updated quote.',
                'rating' => 4,
                'is_featured' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.quote', 'Updated quote.')
            ->assertJsonPath('data.is_featured', true);
    }

    public function test_admin_can_delete_a_testimonial(): void
    {
        $admin = User::factory()->admin()->create();
        $testimonial = Testimonial::factory()->create();

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/testimonials/{$testimonial->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }
}
