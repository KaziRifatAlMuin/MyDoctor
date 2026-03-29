<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class MetricRecordingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_metric_and_store_in_db()
    {
        $user = User::factory()->create();

        $metricTypes = config('health.metric_types');
        $this->assertNotEmpty($metricTypes, 'Metric types config should not be empty');

        $type = array_key_first($metricTypes);
        $fields = $metricTypes[$type]['fields'] ?? [];

        $payload = [
            'metric_type' => $type,
            'recorded_at' => now()->format('Y-m-d\TH:i'),
        ];

        foreach ($fields as $f) {
            $payload["value_$f"] = 123;
        }

        $response = $this->actingAs($user)->post(route('health.metric.store'), $payload);
        $response->assertRedirect(route('health') . '#metrics');

        $this->assertDatabaseHas('health_metrics', [
            'user_id' => $user->id,
            'metric_type' => $type,
        ]);
    }
}
