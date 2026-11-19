<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Models\SuccessStory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class SuccessStoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_stories_in_display_order(): void
    {
        SuccessStory::factory()->create(['name' => 'Second', 'sort_order' => 2]);
        SuccessStory::factory()->create([
            'name' => 'First',
            'destination' => 'Munich, Germany',
            'sort_order' => 1,
            'photo_path' => 'success-stories/first.webp',
        ]);
        SuccessStory::factory()->inactive()->create(['sort_order' => 0]);

        $this->getJson('/api/v1/success-stories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'First')
            ->assertJsonPath('data.0.destination', 'Munich, Germany')
            ->assertJsonPath('data.0.photo_url', config('app.url').'/storage/success-stories/first.webp')
            ->assertJsonStructure(['data' => [['id', 'slug', 'name', 'destination', 'photo_url']]]);
    }

    public function test_limit_parameter_restricts_and_is_validated(): void
    {
        SuccessStory::factory()->count(4)->create();

        $this->getJson('/api/v1/success-stories?limit=3')->assertOk()->assertJsonCount(3, 'data');
        $this->getJson('/api/v1/success-stories?limit=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('limit');
    }
}
