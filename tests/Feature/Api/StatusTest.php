<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class StatusTest extends TestCase
{
    public function test_v1_status_endpoint_responds(): void
    {
        $this->getJson('/api/v1/status')
            ->assertOk()
            ->assertJson(['api' => 'v1', 'status' => 'ok']);
    }

    public function test_frontend_origin_is_allowed_by_cors(): void
    {
        $this->getJson('/api/v1/status', ['Origin' => 'http://localhost:3000'])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
    }

    public function test_unknown_origin_is_not_allowed_by_cors(): void
    {
        $allowed = $this->getJson('/api/v1/status', ['Origin' => 'https://evil.example'])
            ->headers->get('Access-Control-Allow-Origin');

        $this->assertNotSame('https://evil.example', $allowed);
        $this->assertNotSame('*', $allowed);
    }
}
