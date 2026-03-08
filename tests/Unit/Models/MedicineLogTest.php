<?php

namespace Tests\Unit\Models;

use App\Models\Medicine;
use App\Models\User;
use App\Models\MedicineLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_log()
    {
        $medicine = Medicine::factory()->create();
        $user = User::factory()->create();
        
        $log = MedicineLog::create([
            'medicine_id' => $medicine->id,
            'user_id' => $user->id,
            'date' => now(),
            'total_scheduled' => 3,
            'total_taken' => 2,
            'total_missed' => 1
        ]);

        $this->assertDatabaseHas('medicine_logs', [
            'medicine_id' => $medicine->id,
            'user_id' => $user->id,
            'total_scheduled' => 3
        ]);
    }

    public function test_log_belongs_to_medicine()
    {
        $medicine = Medicine::factory()->create();
        $log = MedicineLog::factory()->create(['medicine_id' => $medicine->id]);

        $this->assertInstanceOf(Medicine::class, $log->medicine);
        $this->assertEquals($medicine->id, $log->medicine->id);
    }

    public function test_log_belongs_to_user()
    {
        $user = User::factory()->create();
        $log = MedicineLog::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $log->user);
        $this->assertEquals($user->id, $log->user->id);
    }

    public function test_adherence_rate_attribute()
    {
        $log = new MedicineLog([
            'total_scheduled' => 4,
            'total_taken' => 3,
            'total_missed' => 1
        ]);

        $this->assertEquals(75, $log->adherence_rate);
    }

    public function test_adherence_status_attribute()
    {
        $log = new MedicineLog(['total_scheduled' => 4, 'total_taken' => 4]);
        $status = $log->adherence_status;
        $this->assertEquals('success', $status['class']);
        $this->assertEquals('Excellent', $status['text']);
    }
}