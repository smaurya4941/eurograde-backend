<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

final class HealthTest extends TestCase
{
    public function test_health_endpoint_reports_ok(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.database', 'up');
    }

    public function test_unknown_api_route_returns_json_404(): void
    {
        $this->get('/api/v1/does-not-exist')
            ->assertNotFound()
            ->assertExactJson(['success' => false, 'message' => 'Resource not found.']);
    }
}
