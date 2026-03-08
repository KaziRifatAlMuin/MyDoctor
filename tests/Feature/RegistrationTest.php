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
        $response = $this->get(route('register'));
        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register'), [
            'Name' => 'Test User',              // Uppercase N
            'Email' => 'newuser@example.com',   // Uppercase E
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',   // Database uses lowercase
            'name' => 'Test User'                // Database uses lowercase
        ]);
    }

    #[Test]
    public function registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('register'), [
            'Name' => 'Another User',            // Uppercase N
            'Email' => 'existing@example.com',   // Uppercase E
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('Email');  // Uppercase E for error key
        
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());
    }

    #[Test]
    public function registration_fails_without_name(): void
    {
        $response = $this->post(route('register'), [
            'Name' => '',                         // Uppercase N
            'Email' => 'noname@example.com',       // Uppercase E
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('Name');  // Uppercase N for error key
    }

    #[Test]
    public function registration_fails_without_email(): void
    {
        $response = $this->post(route('register'), [
            'Name' => 'No Email User',            // Uppercase N
            'Email' => '',                          // Uppercase E
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('Email');  // Uppercase E for error key
    }

    #[Test]
    public function registration_fails_with_invalid_email_format(): void
    {
        $response = $this->post(route('register'), [
            'Name' => 'Bad Email',                 // Uppercase N
            'Email' => 'not-an-email',              // Uppercase E
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('Email');  // Uppercase E for error key
    }

    #[Test]
    public function registration_fails_with_password_too_short(): void
    {
        $response = $this->post(route('register'), [
            'Name' => 'Short Pass',                // Uppercase N
            'Email' => 'shortpass@example.com',     // Uppercase E
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_fails_when_passwords_do_not_match(): void
    {
        $response = $this->post(route('register'), [
            'Name' => 'Mismatch User',             // Uppercase N
            'Email' => 'mismatch@example.com',      // Uppercase E
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function registration_stores_optional_fields(): void
    {
        $response = $this->post(route('register'), [
            'Name' => 'Complete User',              // Uppercase N
            'Email' => 'complete@example.com',       // Uppercase E
            'Phone' => '01712345678',                 // Uppercase P
            'DateOfBirth' => '1995-06-15',            // Uppercase D, B
            'Occupation' => 'Engineer',               // Uppercase O
            'BloodGroup' => 'O+',                      // Uppercase B, G
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('users', [
            'email' => 'complete@example.com',        // Database uses lowercase
            'phone' => '01712345678',                  // Database uses lowercase
            'occupation' => 'Engineer',                // Database uses lowercase
            'blood_group' => 'O+',                      // Database uses lowercase
        ]);
    }

    #[Test]
    public function registration_rejects_invalid_blood_group(): void
    {
        $response = $this->post(route('register'), [
            'Name' => 'Bad Blood',                    
            'Email' => 'badblood@example.com',         
            'BloodGroup' => 'X+',                       
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('BloodGroup');  
    }
}