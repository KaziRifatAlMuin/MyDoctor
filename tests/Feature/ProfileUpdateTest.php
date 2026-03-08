<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests specifically for the profile.update (PATCH /profile/update) endpoint
 * which was completely untested in the existing ProfileTest.php.
 */
class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────
    // Guest access
    // ──────────────────────────────────────────────────

    #[Test]
    public function guest_cannot_patch_profile_update(): void
    {
        $this->patch(route('profile.update'), ['name' => 'Hacker'])
             ->assertRedirect(route('login'));
    }

    // ──────────────────────────────────────────────────
    // Happy-path
    // ──────────────────────────────────────────────────

    #[Test]
    public function authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);

        $this->actingAs($user)
             ->patch(route('profile.update'), [
                 'name'          => 'New Name',
                 'date_of_birth' => '1990-05-15',
                 'phone'         => '01712345678',
                 'occupation'    => 'Developer',
                 'blood_group'   => 'O+',
             ])
             ->assertRedirect(route('profile'))
             ->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('Developer', $user->occupation);
        $this->assertEquals('O+', $user->blood_group);
    }

    #[Test]
    public function profile_update_persists_all_fields_in_database(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.update'), [
                 'name'          => 'Jane Doe',
                 'date_of_birth' => '1985-12-01',
                 'phone'         => '01987654321',
                 'occupation'    => 'Nurse',
                 'blood_group'   => 'A-',
             ]);

        $this->assertDatabaseHas('users', [
            'id'         => $user->id,
            'name'       => 'Jane Doe',
            'phone'      => '01987654321',
            'occupation' => 'Nurse',
            'blood_group'=> 'A-',
        ]);
    }

    #[Test]
    public function profile_update_allows_nullable_optional_fields(): void
    {
        $user = User::factory()->create([
            'date_of_birth' => '1990-01-01',
            'phone'         => '01700000000',
            'occupation'    => 'Pilot',
            'blood_group'   => 'B+',
        ]);

        $this->actingAs($user)
             ->patch(route('profile.update'), [
                 'name'          => 'Minimal User',
                 // optional fields omitted
             ])
             ->assertRedirect(route('profile'));

        $user->refresh();
        $this->assertEquals('Minimal User', $user->name);
        $this->assertNull($user->date_of_birth);
        $this->assertNull($user->phone);
    }

    // ──────────────────────────────────────────────────
    // Validation failures — name
    // ──────────────────────────────────────────────────

    #[Test]
    public function profile_update_fails_when_name_is_missing(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.update'), ['name' => ''])
             ->assertSessionHasErrors('name');
    }

    #[Test]
    public function profile_update_fails_when_name_exceeds_255_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.update'), ['name' => str_repeat('A', 256)])
             ->assertSessionHasErrors('name');
    }

    #[Test]
    public function profile_update_accepts_name_of_exactly_255_characters(): void   // boundary OK
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.update'), ['name' => str_repeat('A', 255)])
             ->assertRedirect(route('profile'))
             ->assertSessionMissing('errors');
    }

    // ──────────────────────────────────────────────────
    // Validation failures — date_of_birth
    // ──────────────────────────────────────────────────

    #[Test]
    public function profile_update_fails_when_date_of_birth_is_today(): void  // must be BEFORE today
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.update'), [
                 'name'          => 'Test User',
                 'date_of_birth' => now()->format('Y-m-d'),
             ])
             ->assertSessionHasErrors('date_of_birth');
    }

    #[Test]
    public function profile_update_fails_when_date_of_birth_is_in_the_future(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.update'), [
                 'name'          => 'Test User',
                 'date_of_birth' => now()->addYear()->format('Y-m-d'),
             ])
             ->assertSessionHasErrors('date_of_birth');
    }

    #[Test]
    public function profile_update_accepts_past_date_of_birth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.update'), [
                 'name'          => 'Test User',
                 'date_of_birth' => '1990-06-15',
             ])
             ->assertRedirect(route('profile'))
             ->assertSessionMissing('errors');
    }

    // ──────────────────────────────────────────────────
    // Validation failures — blood_group
    // ──────────────────────────────────────────────────

    #[Test]
    public function profile_update_fails_with_invalid_blood_group(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.update'), [
                 'name'        => 'Test User',
                 'blood_group' => 'Z+',
             ])
             ->assertSessionHasErrors('blood_group');
    }

    #[Test]
    public function profile_update_accepts_every_valid_blood_group(): void
    {
        $validGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        foreach ($validGroups as $group) {
            $user = User::factory()->create();

            $this->actingAs($user)
                 ->patch(route('profile.update'), [
                     'name'        => 'Blood Group Test',
                     'blood_group' => $group,
                 ])
                 ->assertRedirect(route('profile'));
        }
    }

    // ──────────────────────────────────────────────────
    // Data isolation — cannot update another user's profile
    // ──────────────────────────────────────────────────

    #[Test]
    public function profile_update_only_modifies_the_authenticated_user(): void
    {
        $userA = User::factory()->create(['name' => 'Alice']);
        $userB = User::factory()->create(['name' => 'Bob']);

        $this->actingAs($userA)
             ->patch(route('profile.update'), ['name' => 'Alice Updated']);

        $userB->refresh();
        $this->assertEquals('Bob', $userB->name);   // unchanged
    }
}
