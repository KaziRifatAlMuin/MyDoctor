<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Symptom;
use App\Models\Translation;

class EndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_translations_and_user_workflow()
    {
        // Seed translations (idempotent)
        $this->seed(\Database\Seeders\TranslationSeeder::class);

        $this->assertGreaterThan(0, \App\Models\Translation::count());

        // Create a user and visit health page
        $user = User::factory()->create();

        $first = Translation::first();
        $this->assertNotNull($first, 'Expected at least one translation after seeding');

        $response = $this->actingAs($user)->get(route('health'));
        $response->assertStatus(200);

        // The page should render at least one Bangla translation seeded earlier
        $response->assertSee($first->value);

        // Post a symptom using an existing English key
        $symptomData = [
            'symptom_name' => $first->key,
            'severity_level' => 4,
            'recorded_at' => now()->format('Y-m-d\TH:i'),
            'note' => 'Integration test symptom',
        ];

        $post = $this->actingAs($user)->post(route('health.symptom.store'), $symptomData);
        $post->assertRedirect(route('health') . '#symptomsPane');

        $catalogSymptom = Symptom::query()->where('name', $first->key)->first();
        $this->assertNotNull($catalogSymptom);

        $this->assertDatabaseHas('user_symptoms', [
            'user_id' => $user->id,
            'symptom_id' => $catalogSymptom->id,
        ]);

        // Visit health page again and ensure symptom English name appears
        $resp2 = $this->actingAs($user)->get(route('health'));
        $resp2->assertStatus(200);
        $resp2->assertSee($first->key);
    }
}
