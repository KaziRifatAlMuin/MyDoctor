<?php

namespace Tests\Unit;

use App\Models\HealthMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthMetricModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function health_metric_has_correct_fillable_attributes(): void
    {
        $expected = ['user_id', 'metric_type', 'recorded_at', 'value'];
        $this->assertEquals($expected, (new HealthMetric())->getFillable());
    }

    #[Test]
    public function health_metric_casts_value_to_array(): void
    {
        $casts = (new HealthMetric())->getCasts();
        $this->assertArrayHasKey('value', $casts);
        $this->assertEquals('array', $casts['value']);
    }

    #[Test]
    public function health_metric_casts_recorded_at_to_datetime(): void
    {
        $casts = (new HealthMetric())->getCasts();
        $this->assertArrayHasKey('recorded_at', $casts);
        $this->assertEquals('datetime', $casts['recorded_at']);
    }

    #[Test]
    public function health_metric_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $metric = HealthMetric::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $metric->user());
        $this->assertEquals($user->id, $metric->user->id);
    }

    #[Test]
    public function health_metric_stores_value_as_json_array(): void
    {
        $user = User::factory()->create();
        $metric = HealthMetric::factory()->create([
            'user_id'     => $user->id,
            'metric_type' => 'blood_pressure',
            'value'       => ['systolic' => 120, 'diastolic' => 80, 'unit' => 'mmHg'],
            'recorded_at' => now(),
        ]);

        $fresh = HealthMetric::find($metric->id);
        $this->assertIsArray($fresh->value);
        $this->assertEquals(120, $fresh->value['systolic']);
    }

    #[Test]
    public function health_metrics_can_be_filtered_by_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        HealthMetric::factory()->count(3)->create(['user_id' => $userA->id]);
        HealthMetric::factory()->count(2)->create(['user_id' => $userB->id]);

        $this->assertEquals(3, HealthMetric::where('user_id', $userA->id)->count());
        $this->assertEquals(2, HealthMetric::where('user_id', $userB->id)->count());
    }
}
