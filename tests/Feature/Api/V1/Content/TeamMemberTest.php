<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Content;

use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TeamMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_active_members_in_display_order(): void
    {
        TeamMember::factory()->create(['name' => 'Second', 'sort_order' => 2]);
        TeamMember::factory()->create(['name' => 'First', 'sort_order' => 1, 'photo_path' => 'team/first.webp']);
        TeamMember::factory()->inactive()->create(['name' => 'Hidden', 'sort_order' => 0]);

        $this->getJson('/api/v1/team-members')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'First')
            ->assertJsonPath('data.1.name', 'Second')
            ->assertJsonPath('data.0.photo_url', config('app.url').'/storage/team/first.webp')
            ->assertJsonStructure(['data' => [['id', 'slug', 'name', 'role', 'bio', 'photo_url', 'socials']]]);
    }

    public function test_limit_parameter_restricts_results(): void
    {
        TeamMember::factory()->count(5)->create();

        $this->getJson('/api/v1/team-members?limit=2')->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_limit_is_validated(): void
    {
        $this->getJson('/api/v1/team-members?limit=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('limit');
    }

    public function test_cached_listing_is_refreshed_when_content_changes(): void
    {
        $member = TeamMember::factory()->create(['name' => 'Before']);
        $this->getJson('/api/v1/team-members')->assertJsonPath('data.0.name', 'Before');

        $member->update(['name' => 'After']);

        $this->getJson('/api/v1/team-members')->assertJsonPath('data.0.name', 'After');
    }
}
