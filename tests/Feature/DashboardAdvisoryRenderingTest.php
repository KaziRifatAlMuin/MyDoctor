<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardAdvisoryRenderingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dashboard_includes_live_advisory_hook_and_renderer(): void
    {
        // Fake the external API calls to prevent network errors
        Http::fake([
            'https://geocoding-api.open-meteo.com/*' => Http::response([
                'results' => [
                    [
                        'latitude' => 22.8456,
                        'longitude' => 89.5403,
                    ],
                ],
            ], 200),
            'https://api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 30,
                    'apparent_temperature' => 35,
                    'relative_humidity_2m' => 60,
                    'weather_code' => 2,
                    'wind_speed_10m' => 3,
                    'precipitation' => 0,
                    'time' => now()->toIso8601String(),
                ],
                'daily' => [
                    'temperature_2m_max' => [33],
                    'temperature_2m_min' => [25],
                    'precipitation_probability_max' => [10],
                    'precipitation_sum' => [0],
                ],
            ], 200),
            'https://air-quality-api.open-meteo.com/*' => Http::response([
                'current' => [
                    'pm10' => 40,
                    'pm2_5' => 30,
                    'us_aqi' => 120,
                    'time' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        $user = User::factory()->create();

        // Ensure address present so dashboard shows live environment section
        $user->address()->update([
            'division' => 'Khulna',
            'district' => 'Khulna',
            'upazila' => 'Fultola',
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        $content = (string) $response->getContent();

        $this->assertStringContainsString('id="liveEnvAdvisory"', $content);
        $this->assertStringContainsString('function renderChatbotMarkup', $content);
        $this->assertStringContainsString('renderChatbotMarkup(', $content);
    }
}