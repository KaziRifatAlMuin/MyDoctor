<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Medicine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_medicine_pages()
    {
        $routes = [
            route('medicine.my-medicines'),
            route('medicine.add'),
            route('medicine.reminders'),
            route('medicine.logs'),
            route('medicine.schedules'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_guest_cannot_perform_medicine_actions()
    {
        $actions = [
            ['post', route('medicine.store')],
            ['put', route('medicine.update', 1)],
            ['delete', route('medicine.destroy', 1)],
        ];

        foreach ($actions as $action) {
            [$method, $route] = $action;
            $response = $this->$method($route);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_user_can_access_medicine_pages()
    {
        $user = User::factory()->create();
        
        $routes = [
            route('medicine.my-medicines'),
            route('medicine.add'),
            route('medicine.reminders'),
            route('medicine.logs'),
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertStatus(200);
        }
    }
}