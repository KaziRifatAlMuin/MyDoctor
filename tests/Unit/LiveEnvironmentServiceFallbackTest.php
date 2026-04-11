<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\LiveEnvironmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LiveEnvironmentServiceFallbackTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function for_user_returns_missing_address_when_not_set(): void
    {
        $user = User::factory()->create();

        $payload = app(LiveEnvironmentService::class)->forUser($user);

        $this->assertFalse((bool) data_get($payload, 'available'));
        $this->assertSame('missing_address', data_get($payload, 'reason'));
    }

    #[Test]
    public function for_user_uses_fallback_coordinates_when_geocoding_fails(): void
    {
        $user = User::factory()->create();
        $user->address()->update([
            'division' => 'Khulna',
            'district' => 'Khulna',
            'upazila' => 'Fultola',
        ]);

        // Geocoding fails (500) but weather and air quality succeed for fallback coords
        Http::fake([
            'https://geocoding-api.open-meteo.com/*' => Http::response([], 500),
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

        $payload = app(LiveEnvironmentService::class)->forUser($user);

        $this->assertTrue((bool) data_get($payload, 'available'));
        $this->assertArrayHasKey('insights', $payload);
        $this->assertStringContainsString('Khulna', data_get($payload, 'location_label'));
    }
}
