<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function login_page_is_accessible_to_guests(): void
    {
        $this->get(route('login'))->assertStatus(200);
    }

    #[Test]
    public function user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com']);
        // UserFactory defaults password to Hash::make('password')

        $this->post(route('login'), [
            'Email'    => 'login@example.com',
            'password' => 'password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'wrongpass@example.com']);

        $this->post(route('login'), [
            'Email'    => 'wrongpass@example.com',
            'password' => 'wrongpassword',
        ])->assertSessionHasErrors('Email');

        $this->assertGuest();
    }

    #[Test]
    public function login_fails_with_nonexistent_email(): void
    {
        $this->post(route('login'), [
            'Email'    => 'nobody@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('Email');

        $this->assertGuest();
    }

    #[Test]
    public function login_requires_email_field(): void
    {
        $this->post(route('login'), [
            'Email'    => '',
            'password' => 'password',
        ])->assertSessionHasErrors('Email');
    }

    #[Test]
    public function login_requires_password_field(): void
    {
        $this->post(route('login'), [
            'Email'    => 'someone@example.com',
            'password' => '',
        ])->assertSessionHasErrors('password');
    }

    #[Test]
    public function authenticated_user_is_redirected_away_from_login_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('login'))
             ->assertRedirect('/');
    }

    #[Test]
    public function user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('logout'))
             ->assertRedirect('/');

        $this->assertGuest();
    }
}
