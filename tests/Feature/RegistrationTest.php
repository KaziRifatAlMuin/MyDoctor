<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function requiredRegistrationFields(): array
    {
        return [
            'Gender' => 'other',
            'DivisionId' => 30,
            'Division' => 'Dhaka',
            'DistrictId' => 3026,
            'District' => 'Dhaka',
            'UpazilaId' => 302631,
            'Upazila' => 'Dhanmondi',
        ];
    }

    #[Test]
    public function registration_page_is_accessible(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Test User',
            'Email' => 'newuser@example.com',
            'password' => 'pass1234', // 7 chars - valid with min:4
            'password_confirmation' => 'pass1234',
        ]));

        // After registration, user is redirected to email verification notice
        $response->assertRedirect(route('verification.notice'));
        
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'Test User'
        ]);
    }

    #[Test]
    public function registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Another User',
            'Email' => 'existing@example.com',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]));

        $response->assertSessionHasErrors('Email');
        
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }

    #[Test]
    public function registration_fails_without_name(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => '',
            'Email' => 'noname@example.com',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]));

        $response->assertSessionHasErrors('Name');
    }

    #[Test]
    public function registration_fails_without_email(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'No Email User',
            'Email' => '',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]));

        $response->assertSessionHasErrors('Email');
    }

    #[Test]
    public function registration_fails_with_invalid_email_format(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Bad Email',
            'Email' => 'not-an-email',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]));

        $response->assertSessionHasErrors('Email');
    }

    #[Test]
    public function registration_fails_with_password_too_short(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Short Pass',
            'Email' => 'shortpass@example.com',
            'password' => '123', // 3 chars - less than min:4
            'password_confirmation' => '123',
        ]));

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_fails_when_passwords_do_not_match(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Mismatch User',
            'Email' => 'mismatch@example.com',
            'password' => 'pass1234',
            'password_confirmation' => 'differentpassword',
        ]));

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_stores_optional_fields(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Complete User',
            'Email' => 'complete@example.com',
            'Phone' => '01712345678',
            'DateOfBirth' => '1995-06-15',
            'Occupation' => 'Engineer',
            'BloodGroup' => 'O+',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]));

        $response->assertRedirect(route('verification.notice'));
        
        $this->assertDatabaseHas('users', [
            'email' => 'complete@example.com',
            'phone' => '01712345678',
            'occupation' => 'Engineer',
            'blood_group' => 'O+',
        ]);
    }

    #[Test]
    public function registration_rejects_invalid_blood_group(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Bad Blood',
            'Email' => 'badblood@example.com',
            'BloodGroup' => 'X+',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]));

        $response->assertSessionHasErrors('BloodGroup');
    }

    #[Test]
    public function registration_fails_with_name_exceeding_255_characters(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => str_repeat('A', 256),
            'Email' => 'longname@example.com',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]));

        $response->assertSessionHasErrors('Name');
    }

    #[Test]
    public function registration_accepts_name_of_exactly_255_characters(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => str_repeat('A', 255),
            'Email' => 'exactname@example.com',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]));

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', ['email' => 'exactname@example.com']);
    }

    #[Test]
    public function registration_succeeds_with_password_of_exactly_4_characters(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Exact Pass',
            'Email' => 'exact4@example.com',
            'password' => 'pass', // 4 chars - minimum allowed
            'password_confirmation' => 'pass',
        ]));

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', ['email' => 'exact4@example.com']);
    }

    #[Test]
    public function registration_fails_with_password_of_3_characters(): void
    {
        $response = $this->post(route('register'), array_merge($this->requiredRegistrationFields(), [
            'Name' => 'Short Pass',
            'Email' => 'pass3@example.com',
            'password' => 'pas', // 3 chars - below minimum
            'password_confirmation' => 'pas',
        ]));

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function authenticated_user_visiting_register_page_is_redirected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('register'))
             ->assertRedirect(route('dashboard'));
    }

    #[Test]
    public function authenticated_user_posting_to_register_is_redirected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('register'), [
                 ...$this->requiredRegistrationFields(),
                 'Name' => 'Another User',
                 'Email' => 'another@example.com',
                 'password' => 'pass1234',
                 'password_confirmation' => 'pass1234',
             ])
             ->assertRedirect(route('dashboard'));
    }
}