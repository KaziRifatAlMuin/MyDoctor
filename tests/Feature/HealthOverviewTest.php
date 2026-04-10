<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class HealthOverviewTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function health_index_loads_with_configured_symptom_labels(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('health'));

        $response->assertStatus(200);
        $response->assertViewHas('symptomsList', config('health.symptoms', []));
    }
}
