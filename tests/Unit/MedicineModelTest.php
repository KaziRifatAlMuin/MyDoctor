<?php

namespace Tests\Unit;

use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MedicineModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function medicine_has_correct_fillable_attributes(): void
    {
        $expected = ['user_id', 'medicine_name', 'type', 'value_per_dose', 'unit', 'rule', 'dose_limit'];
        $this->assertEquals($expected, (new Medicine())->getFillable());
    }

    #[Test]
    public function medicine_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $medicine->user());
        $this->assertEquals($user->id, $medicine->user->id);
    }

    #[Test]
    public function medicine_has_many_schedules(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        MedicineSchedule::factory()->count(3)->create(['medicine_id' => $medicine->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $medicine->schedules());
        $this->assertEquals(3, $medicine->schedules()->count());
    }

    #[Test]
    public function medicine_has_many_logs(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        // use distinct dates to satisfy the unique(medicine_id, user_id, date) constraint
        MedicineLog::factory()->create([
            'medicine_id' => $medicine->id,
            'user_id'     => $user->id,
            'date'        => '2026-01-01',
        ]);
        MedicineLog::factory()->create([
            'medicine_id' => $medicine->id,
            'user_id'     => $user->id,
            'date'        => '2026-01-02',
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $medicine->logs());
        $this->assertEquals(2, $medicine->logs()->count());
    }

    #[Test]
    public function medicine_can_be_created_with_all_fields(): void
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create([
            'user_id'        => $user->id,
            'medicine_name'  => 'Paracetamol',
            'type'           => 'Tablet',
            'value_per_dose' => 500.00,
            'unit'           => 'mg',
            'rule'           => 'After meal',
            'dose_limit'     => 3,
        ]);

        $this->assertDatabaseHas('medicines', [
            'medicine_name' => 'Paracetamol',
            'user_id'       => $user->id,
        ]);
        $this->assertEquals('Tablet', $medicine->type);
        $this->assertEquals(500.00, $medicine->value_per_dose);
    }

    #[Test]
    public function medicine_schedule_marks_active_status(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        $active   = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id, 'is_active' => true]);
        $inactive = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id, 'is_active' => false]);

        $this->assertTrue((bool) $active->is_active);
        $this->assertFalse((bool) $inactive->is_active);
    }
}
