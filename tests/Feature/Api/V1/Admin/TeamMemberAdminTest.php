<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TeamMemberAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->getJson('/api/v1/admin/team-members')
            ->assertForbidden();
    }

    public function test_admin_can_list_including_inactive(): void
    {
        $admin = User::factory()->admin()->create();
        TeamMember::factory()->inactive()->create();

        $this->actingAs($admin, 'web')
            ->getJson('/api/v1/admin/team-members')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_create_a_team_member(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->postJson('/api/v1/admin/team-members', [
                'name' => 'Jane Counselor',
                'role' => 'Senior Counselor',
            ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'jane-counselor');

        $this->assertDatabaseHas('team_members', ['name' => 'Jane Counselor']);
    }

    public function test_admin_can_update_a_team_member(): void
    {
        $admin = User::factory()->admin()->create();
        $teamMember = TeamMember::factory()->create();

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/team-members/{$teamMember->id}", [
                'name' => 'Updated Name',
                'slug' => $teamMember->slug,
                'role' => $teamMember->role,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.is_active', false);
    }

    public function test_admin_can_set_social_links(): void
    {
        $admin = User::factory()->admin()->create();
        $teamMember = TeamMember::factory()->create();

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/team-members/{$teamMember->id}", [
                'name' => $teamMember->name,
                'slug' => $teamMember->slug,
                'role' => $teamMember->role,
                'socials' => [
                    ['network' => 'facebook', 'url' => 'https://facebook.com/jane'],
                    ['network' => 'instagram', 'url' => 'https://instagram.com/jane'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.socials', [
                ['network' => 'facebook', 'url' => 'https://facebook.com/jane'],
                ['network' => 'instagram', 'url' => 'https://instagram.com/jane'],
            ]);

        $this->assertDatabaseHas('team_members', ['id' => $teamMember->id]);
    }

    public function test_admin_can_clear_all_social_links_via_the_provided_marker(): void
    {
        $admin = User::factory()->admin()->create();
        $teamMember = TeamMember::factory()->create([
            'socials' => [['network' => 'facebook', 'url' => 'https://facebook.com/jane']],
        ]);

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/team-members/{$teamMember->id}", [
                'name' => $teamMember->name,
                'slug' => $teamMember->slug,
                'role' => $teamMember->role,
                'socials_provided' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('data.socials', []);

        $this->assertSame([], $teamMember->fresh()->socials);
    }

    public function test_admin_leaves_social_links_untouched_without_the_provided_marker(): void
    {
        $admin = User::factory()->admin()->create();
        $teamMember = TeamMember::factory()->create([
            'socials' => [['network' => 'facebook', 'url' => 'https://facebook.com/jane']],
        ]);

        $this->actingAs($admin, 'web')
            ->putJson("/api/v1/admin/team-members/{$teamMember->id}", [
                'name' => 'Renamed Only',
                'slug' => $teamMember->slug,
                'role' => $teamMember->role,
            ])
            ->assertOk();

        $this->assertEquals(
            [['network' => 'facebook', 'url' => 'https://facebook.com/jane']],
            $teamMember->fresh()->socials,
        );
    }

    public function test_admin_can_delete_a_team_member(): void
    {
        $admin = User::factory()->admin()->create();
        $teamMember = TeamMember::factory()->create();

        $this->actingAs($admin, 'web')
            ->deleteJson("/api/v1/admin/team-members/{$teamMember->id}")
            ->assertNoContent();

        $this->assertSoftDeleted('team_members', ['id' => $teamMember->id]);
    }
}
