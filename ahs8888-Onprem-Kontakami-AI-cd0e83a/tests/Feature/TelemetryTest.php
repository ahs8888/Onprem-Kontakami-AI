<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TelemetryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test telemetry endpoint accessibility
     */
    public function test_telemetry_endpoint_is_accessible(): void
    {
        $response = $this->get('/status/local');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'queue_size',
            'recordings',
            'uploads',
        ]);
    }

    /**
     * Test telemetry returns correct data structure
     */
    public function test_telemetry_returns_correct_structure(): void
    {
        $response = $this->get('/status/local');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('recordings', $data);
        $this->assertArrayHasKey('total', $data['recordings']);
        $this->assertArrayHasKey('pending', $data['recordings']);
        $this->assertArrayHasKey('completed', $data['recordings']);
    }

    /**
     * Test telemetry logging
     */
    public function test_telemetry_logs_are_created(): void
    {
        $service = app(\App\Services\TelemetryService::class);
        
        $service->logMetrics([
            'test_metric' => 'test_value',
            'count' => 42,
        ]);

        $this->assertDatabaseHas('telemetry_logs', [
            'metric_type' => 'general',
        ]);
    }
}
