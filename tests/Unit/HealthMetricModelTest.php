<?php

namespace Tests\Unit;

use App\Models\HealthMetric;
use App\Models\UserHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HealthMetricModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function health_metric_has_correct_fillable_attributes(): void
    {
        $expected = ['metric_name', 'fields'];
        $this->assertEquals($expected, (new HealthMetric())->getFillable());
    }

    #[Test]
    public function health_metric_casts_fields_to_array(): void
    {
        $casts = (new HealthMetric())->getCasts();
        $this->assertArrayHasKey('fields', $casts);
        $this->assertEquals('array', $casts['fields']);
    }

    #[Test]
    public function health_metric_has_user_health_records_relationship(): void
    {
        $metric = HealthMetric::factory()->create([
            'metric_name' => 'heart_rate',
            'fields' => ['bpm'],
        ]);

        UserHealth::factory()->count(2)->create(['health_metric_id' => $metric->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $metric->userHealthRecords());
        $this->assertEquals(2, $metric->userHealthRecords()->count());
    }

    #[Test]
    public function health_metric_stores_fields_as_json_array(): void
    {
        $metric = HealthMetric::factory()->create([
            'metric_name' => 'blood_pressure',
            'fields' => ['systolic', 'diastolic'],
        ]);

        $fresh = HealthMetric::find($metric->id);
        $this->assertIsArray($fresh->fields);
        $this->assertEquals('systolic', $fresh->fields[0]);
    }

    #[Test]
    public function health_metrics_can_be_filtered_by_metric_name(): void
    {
        HealthMetric::factory()->create(['metric_name' => 'blood_pressure', 'fields' => ['systolic', 'diastolic']]);
        HealthMetric::factory()->create(['metric_name' => 'heart_rate', 'fields' => ['bpm']]);

        $this->assertEquals(1, HealthMetric::where('metric_name', 'blood_pressure')->count());
        $this->assertEquals(1, HealthMetric::where('metric_name', 'heart_rate')->count());
    }
}
