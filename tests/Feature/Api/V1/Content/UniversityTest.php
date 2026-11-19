<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Models\Country;
use App\Models\University;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UniversityTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_universities_of_active_countries(): void
    {
        $germany = Country::factory()->create(['slug' => 'germany', 'name' => 'Germany']);
        $hidden = Country::factory()->inactive()->create();

        University::factory()->for($germany)->create(['name' => 'B', 'sort_order' => 2]);
        University::factory()->for($germany)->create(['name' => 'A', 'sort_order' => 1, 'logo_path' => 'universities/a.webp']);
        University::factory()->for($germany)->inactive()->create();
        University::factory()->for($hidden)->create();

        $this->getJson('/api/v1/universities')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'A')
            ->assertJsonPath('data.0.type', 'public')
            ->assertJsonPath('data.0.country', ['slug' => 'germany', 'name' => 'Germany'])
            ->assertJsonPath('data.0.logo_url', config('app.url').'/storage/universities/a.webp');
    }

    public function test_filters_by_country_partners_and_limit(): void
    {
        $germany = Country::factory()->create(['slug' => 'germany']);
        $italy = Country::factory()->create(['slug' => 'italy']);
        University::factory()->for($germany)->count(2)->create();
        University::factory()->for($germany)->notPartner()->create();
        University::factory()->for($italy)->create();

        $this->getJson('/api/v1/universities?country=germany')->assertOk()->assertJsonCount(3, 'data');
        $this->getJson('/api/v1/universities?country=germany&partners=1')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/universities?limit=1')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_query_is_validated(): void
    {
        $this->getJson('/api/v1/universities?country=Not%20A%20Slug&limit=0')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['country', 'limit']);
    }
}
