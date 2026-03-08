<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medicine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_medicines_page()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('medicine.my-medicines'));

        $response->assertStatus(200);
        $response->assertViewIs('medicine.my-medicines');
    }

    public function test_user_can_create_medicine()
    {
        $user = User::factory()->create();
        
        $data = [
            'medicine_name' => 'Aspirin',
            'type' => 'tablet',
            'value_per_dose' => 100,
            'unit' => 'mg',
            'rule' => 'after_food',
            'dose_limit' => 4
        ];

        $response = $this->actingAs($user)->post(route('medicine.store'), $data);

        $response->assertRedirect(route('medicine.my-medicines'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('medicines', [
            'user_id' => $user->id,
            'medicine_name' => 'Aspirin'
        ]);
    }

    public function test_user_can_update_medicine()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        
        $data = [
            'medicine_name' => 'Updated Medicine',
            'type' => 'capsule',
            'value_per_dose' => 200,
            'unit' => 'mg',
            'rule' => 'before_food',
            'dose_limit' => 2
        ];

        $response = $this->actingAs($user)->put(route('medicine.update', $medicine->id), $data);

        $response->assertRedirect(route('medicine.my-medicines'));
        
        $this->assertDatabaseHas('medicines', [
            'id' => $medicine->id,
            'medicine_name' => 'Updated Medicine'
        ]);
    }

    public function test_user_can_delete_medicine()
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('medicine.destroy', $medicine->id));

        $response->assertRedirect(route('medicine.my-medicines'));
        $this->assertDatabaseMissing('medicines', ['id' => $medicine->id]);
    }

    public function test_user_cannot_access_other_users_medicine()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('medicine.edit', $medicine->id));

        $response->assertStatus(404);
    }
}