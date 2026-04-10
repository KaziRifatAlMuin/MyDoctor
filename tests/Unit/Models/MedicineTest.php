<?php

namespace Tests\Unit\Models;

use App\Models\Medicine;
use App\Models\User;
use App\Models\MedicineSchedule;
use App\Models\MedicineLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_medicine()
    {
        $user = User::factory()->create();
        
        $medicine = Medicine::create([
            'user_id' => $user->id,
            'medicine_name' => 'Test Medicine',
            'type' => 'tablet',
            'value_per_dose' => 500,
            'unit' => 'mg',
            'rule' => 'after_food',
            'dose_limit' => 3
        ]);

        $this->assertDatabaseHas('medicines', [
            'medicine_name' => 'Test Medicine',
            'user_id' => $user->id
        ]);
    }

    public function test_medicine_belongs_to_user()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $medicine->user);
        $this->assertEquals($user->id, $medicine->user->id);
    }

    public function test_medicine_has_many_schedules()
    {
        $medicine = Medicine::factory()->create();
        $schedule = MedicineSchedule::factory()->create(['medicine_id' => $medicine->id]);

        $this->assertTrue($medicine->schedules->contains($schedule));
        $this->assertEquals(1, $medicine->schedules->count());
    }

    public function test_medicine_has_many_logs()
    {
        $medicine = Medicine::factory()->create();
        $log = MedicineLog::factory()->create(['medicine_id' => $medicine->id]);

        $this->assertTrue($medicine->logs->contains($log));
        $this->assertEquals(1, $medicine->logs->count());
    }

    public function test_medicine_type_label_attribute()
    {
        $medicine = new Medicine(['type' => 'tablet']);
        $this->assertEquals('Tablet', $medicine->type_label);
    }

    public function test_medicine_rule_label_attribute()
    {
        $medicine = new Medicine(['rule' => 'after_food']);
        $this->assertEquals('After Food', $medicine->rule_label);
    }
}