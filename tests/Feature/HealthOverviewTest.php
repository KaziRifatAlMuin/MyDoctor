<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Translation;

class HealthOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_index_shows_translations_from_db()
    {
        // Create a user and a translation for a symptom
        $user = User::factory()->create();

        Translation::create(['type' => Translation::TYPE_SYMPTOM, 'key' => 'Cough', 'value' => 'কাশি-টেস্ট']);

        $response = $this->actingAs($user)->get(route('health'));

        $response->assertStatus(200);

        // The Bangla translation should appear somewhere in the rendered page
        $response->assertSee('কাশি-টেস্ট');
    }
}
