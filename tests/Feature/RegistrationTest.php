<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function registration_page_is_accessible(): void
    {
        $this->get(route('register'))->assertStatus(200);
    }

    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        $this->post(route('register'), [
            'Name'                  => 'Test User',
            'Email'                 => 'newuser@example.com',
            'password'              => 'password12',
            'password_confirmation' => 'password12',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com', 'name' => 'Test User']);
    }

    #[Test]
    public function registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->post(route('register'), [
            'Name'                  => 'Another User',
            'Email'                 => 'existing@example.com',
            'password'              => 'password12',
            'password_confirmation' => 'password12',
        ])->assertSessionHasErrors('Email');

        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }

    #[Test]
    public function registration_fails_without_name(): void
    {
        $this->post(route('register'), [
            'Name'                  => '',
            'Email'                 => 'noname@example.com',
            'password'              => 'password12',
            'password_confirmation' => 'password12',
        ])->assertSessionHasErrors('Name');
    }

    #[Test]
    public function registration_fails_without_email(): void
    {
        $this->post(route('register'), [
            'Name'                  => 'No Email User',
            'Email'                 => '',
            'password'              => 'password12',
            'password_confirmation' => 'password12',
        ])->assertSessionHasErrors('Email');
    }

    #[Test]
    public function registration_fails_with_invalid_email_format(): void
    {
        $this->post(route('register'), [
            'Name'                  => 'Bad Email',
            'Email'                 => 'not-an-email',
            'password'              => 'password12',
            'password_confirmation' => 'password12',
        ])->assertSessionHasErrors('Email');
    }

    #[Test]
    public function registration_fails_with_password_too_short(): void
    {
        $this->post(route('register'), [
            'Name'                  => 'Short Pass',
            'Email'                 => 'shortpass@example.com',
            'password'              => 'abc',
            'password_confirmation' => 'abc',
        ])->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_fails_when_passwords_do_not_match(): void
    {
        $this->post(route('register'), [
            'Name'                  => 'Mismatch User',
            'Email'                 => 'mismatch@example.com',
            'password'              => 'password12',
            'password_confirmation' => 'differentpassword',
        ])->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_stores_optional_fields(): void
    {
        $this->post(route('register'), [
            'Name'                  => 'Complete User',
            'Email'                 => 'complete@example.com',
            'Phone'                 => '01712345678',
            'DateOfBirth'           => '1995-06-15',
            'Occupation'            => 'Engineer',
            'BloodGroup'            => 'O+',
            'password'              => 'password12',
            'password_confirmation' => 'password12',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email'      => 'complete@example.com',
            'phone'      => '01712345678',
            'occupation' => 'Engineer',
            'blood_group'=> 'O+',
        ]);
    }

    #[Test]
    public function registration_rejects_invalid_blood_group(): void
    {
        $this->post(route('register'), [
            'Name'                  => 'Bad Blood',
            'Email'                 => 'badblood@example.com',
            'BloodGroup'            => 'X+',
            'password'              => 'password12',
            'password_confirmation' => 'password12',
        ])->assertSessionHasErrors('BloodGroup');
    }
}
