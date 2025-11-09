<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class NologinEndpointTest extends TestCase
{
    public function test_nologin_endpoint_returns_unauthenticated_payload(): void
    {
        $response = $this->getJson('/api/nologin');

        $response->assertStatus(401)->assertExactJson([
            'message' => 'No autenticado',
        ]);
    }
}

