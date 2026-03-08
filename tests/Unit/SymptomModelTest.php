<?php

namespace Tests\Unit;

use App\Models\Symptom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SymptomModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function symptom_has_correct_fillable_attributes(): void
    {
        $expected = ['user_id', 'symptom_name', 'severity_level', 'note', 'recorded_at'];
        $this->assertEquals($expected, (new Symptom())->getFillable());
    }

    #[Test]
    public function symptom_casts_recorded_at_to_datetime(): void
    {
        $casts = (new Symptom())->getCasts();
        $this->assertArrayHasKey('recorded_at', $casts);
        $this->assertEquals('datetime', $casts['recorded_at']);
    }

    #[Test]
    public function symptom_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $symptom->user());
        $this->assertEquals($user->id, $symptom->user->id);
    }

    #[Test]
    public function symptom_can_have_null_severity(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create([
            'user_id'        => $user->id,
            'symptom_name'   => 'Headache',
            'severity_level' => null,
            'recorded_at'    => now(),
        ]);

        $this->assertNull($symptom->severity_level);
    }

    #[Test]
    public function symptom_severity_is_within_valid_range(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create([
            'user_id'        => $user->id,
            'severity_level' => 7,
            'recorded_at'    => now(),
        ]);

        $this->assertGreaterThanOrEqual(1, $symptom->severity_level);
        $this->assertLessThanOrEqual(10, $symptom->severity_level);
    }

    #[Test]
    public function symptoms_can_be_queried_by_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Symptom::factory()->count(4)->create(['user_id' => $userA->id]);
        Symptom::factory()->count(2)->create(['user_id' => $userB->id]);

        $this->assertEquals(4, Symptom::where('user_id', $userA->id)->count());
    }
}
