<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guests_are_redirected_from_profile_page(): void
    {
        $this->get(route('profile'))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_access_profile_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('profile'))
             ->assertStatus(200);
    }

    #[Test]
    public function profile_page_returns_view(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('profile'))
             ->assertViewIs('profile');
    }

    #[Test]
    public function guest_cannot_post_to_logout(): void
    {
        $this->post(route('logout'))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_change_password_successfully(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.password'), [
                 'current_password' => 'abcd1234',
                 'password'         => 'newpassword1',
                 'password_confirmation' => 'newpassword1',
             ])
             ->assertRedirect(route('profile'));

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword1', $user->password));
    }

    #[Test]
    public function password_change_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.password'), [
                 'current_password'      => 'wrong-current',
                 'password'              => 'newpassword1',
                 'password_confirmation' => 'newpassword1',
             ])
             ->assertSessionHasErrors('current_password');
    }

    #[Test]
    public function password_change_fails_when_passwords_do_not_match(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->patch(route('profile.password'), [
                 'current_password'      => 'abcd1234',
                 'password'              => 'newpassword1',
                 'password_confirmation' => 'differentpassword',
             ])
             ->assertSessionHasErrors('password');
    }

    #[Test]
    public function authenticated_user_cannot_delete_account_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->delete(route('profile.destroy'), ['delete_password' => 'wrongpassword'])
             ->assertSessionHasErrors('delete_password');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    #[Test]
    public function authenticated_user_can_delete_account_with_correct_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->delete(route('profile.destroy'), ['delete_password' => 'abcd1234'])
             ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}