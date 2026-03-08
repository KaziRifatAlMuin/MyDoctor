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

    public function test_store_fails_without_medicine_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('medicine.store'), [
                 'type'       => 'tablet',
                 'dose_limit' => 2,
             ])
             ->assertSessionHasErrors('medicine_name');
    }

    public function test_store_fails_with_medicine_name_exceeding_255_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('medicine.store'), [
                 'medicine_name' => str_repeat('A', 256),
             ])
             ->assertSessionHasErrors('medicine_name');
    }

    public function test_store_fails_with_invalid_medicine_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('medicine.store'), [
                 'medicine_name' => 'Unknown Med',
                 'type'          => 'powder',   // not in allowed list
             ])
             ->assertSessionHasErrors('type');
    }

    public function test_store_fails_with_dose_limit_of_zero(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('medicine.store'), [
                 'medicine_name' => 'Zero Dose Med',
                 'dose_limit'    => 0,   // min:1, so 0 fails
             ])
             ->assertSessionHasErrors('dose_limit');
    }

    public function test_store_fails_with_negative_dose_limit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('medicine.store'), [
                 'medicine_name' => 'Negative Med',
                 'dose_limit'    => -1,
             ])
             ->assertSessionHasErrors('dose_limit');
    }

    public function test_store_accepts_dose_limit_of_1(): void  // min boundary
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('medicine.store'), [
                 'medicine_name' => 'Min Dose Med',
                 'dose_limit'    => 1,
             ])
             ->assertRedirect(route('medicine.my-medicines'));

        $this->assertDatabaseHas('medicines', ['user_id' => $user->id, 'dose_limit' => 1]);
    }

    public function test_store_fails_with_invalid_unit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('medicine.store'), [
                 'medicine_name' => 'Bad Unit Med',
                 'unit'          => 'oz',   // not in allowed list
             ])
             ->assertSessionHasErrors('unit');
    }

    public function test_store_fails_with_invalid_rule(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('medicine.store'), [
                 'medicine_name' => 'Bad Rule Med',
                 'rule'          => 'whenever',   // not in allowed list
             ])
             ->assertSessionHasErrors('rule');
    }

    public function test_update_returns_404_for_another_users_medicine(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
             ->put(route('medicine.update', $medicine->id), [
                 'medicine_name' => 'Hijacked',
             ])
             ->assertStatus(404);

        $this->assertDatabaseMissing('medicines', ['medicine_name' => 'Hijacked']);
    }

    public function test_delete_returns_404_for_another_users_medicine(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
             ->delete(route('medicine.destroy', $medicine->id))
             ->assertStatus(404);

        $this->assertDatabaseHas('medicines', ['id' => $medicine->id]);
    }

    public function test_guest_is_redirected_from_medicine_store(): void
    {
        $this->post(route('medicine.store'), ['medicine_name' => 'Test'])
             ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_medicine_index(): void
    {
        $this->get(route('medicine.my-medicines'))
             ->assertRedirect(route('login'));
    }
}