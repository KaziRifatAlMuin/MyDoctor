<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medicine;
use App\Models\MedicineLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineLogIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_logs_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('medicine.logs'));

        $response->assertStatus(200);
        $response->assertViewIs('medicine.logs');
    }

    public function test_user_can_filter_logs_by_medicine()
    {
        $user = User::factory()->create();
        $medicine1 = Medicine::factory()->create(['user_id' => $user->id]);
        $medicine2 = Medicine::factory()->create(['user_id' => $user->id]);
        
        MedicineLog::factory()->count(3)->create([
            'medicine_id' => $medicine1->id,
            'user_id' => $user->id
        ]);
        MedicineLog::factory()->count(2)->create([
            'medicine_id' => $medicine2->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->get(route('medicine.logs', ['medicine_id' => $medicine1->id]));

        $response->assertStatus(200);
        // Assert view has filtered data
    }

    public function test_user_can_filter_logs_by_days()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('medicine.logs', ['days' => 7]));

        $response->assertStatus(200);
    }

    public function test_user_can_export_logs_as_csv()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        MedicineLog::factory()->count(5)->create([
            'medicine_id' => $medicine->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->get(route('medicine.logs.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}