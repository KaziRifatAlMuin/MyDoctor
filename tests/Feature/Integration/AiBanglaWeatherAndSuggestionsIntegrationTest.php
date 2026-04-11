<?php

namespace Tests\Feature\Integration;

use App\Models\Disease;
use App\Models\HealthMetric;
use App\Models\Medicine;
use App\Models\Symptom;
use App\Models\User;
use App\Models\UserDisease;
use App\Models\UserHealth;
use App\Models\UserSymptom;
use App\Services\LiveEnvironmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AiBanglaWeatherAndSuggestionsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function live_environment_fallback_advisory_is_bangla_bulleted_and_health_aware(): void
    {
        $user = User::factory()->create();
        $user->address()->update([
            'division' => 'Khulna',
            'district' => 'Khulna',
            'upazila' => 'Fultola',
        ]);

        $disease = Disease::factory()->create([
            'disease_name' => 'Atrial Fibrillation',
        ]);

        UserDisease::factory()->create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'status' => 'active',
        ]);

        config([
            'services.openrouter.api_key' => '',
            'services.google.api_key' => '',
        ]);

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
                    'relative_humidity_2m' => 65,
                    'apparent_temperature' => 35.4,
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
                    'pm10' => 47.4,
                    'pm2_5' => 32.4,
                    'us_aqi' => 141,
                    'time' => now()->toIso8601String(),
                ],
            ], 200),
        ]);

        $payload = app(LiveEnvironmentService::class)->forUser($user);

        $this->assertTrue((bool) data_get($payload, 'available'), json_encode($payload));

        $advisory = (string) data_get($payload, 'insights.advisory', '');
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $advisory) ?: [])));

        $this->assertCount(3, $lines);
        $this->assertTrue(collect($lines)->every(fn(string $line) => str_starts_with($line, '- ')));
        $this->assertStringContainsString('**', $advisory);
        $this->assertStringContainsString('এট্রিয়াল ফাইব্রিলেশন (Atrial Fibrillation)', $advisory);
        $this->assertStringContainsString('AQI', $advisory);
    }

    #[Test]
    public function about_me_local_fallback_returns_bangla_summary_with_smart_suggestions_section(): void
    {
        $user = User::factory()->create();

        $disease = Disease::factory()->create([
            'disease_name' => 'Atrial Fibrillation',
        ]);
        $symptom = Symptom::factory()->create([
            'name' => 'Productive Cough',
        ]);

        UserDisease::factory()->create([
            'user_id' => $user->id,
            'disease_id' => $disease->id,
            'status' => 'active',
        ]);

        UserSymptom::factory()->create([
            'user_id' => $user->id,
            'symptom_id' => $symptom->id,
            'severity_level' => 6,
            'recorded_at' => now(),
        ]);

        $metric = HealthMetric::factory()->create([
            'metric_name' => 'heart_rate',
            'fields' => ['value'],
        ]);

        UserHealth::factory()->create([
            'user_id' => $user->id,
            'health_metric_id' => $metric->id,
            'metric_type' => 'heart_rate',
            'value' => ['value' => 110, 'unit' => 'bpm'],
            'recorded_at' => now(),
        ]);

        Medicine::factory()->create([
            'user_id' => $user->id,
            'medicine_name' => 'Metoprolol',
        ]);

        config([
            'services.openrouter.api_key' => '',
            'services.google.api_key' => '',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.about_me'))
            ->assertOk();

        $reply = (string) $response->json('reply');

        $this->assertStringContainsString('**রোগের অবস্থা:**', $reply);
        $this->assertStringContainsString('**স্মার্ট পরামর্শ**', $reply);

        preg_match_all('/^- \*\*.+/m', $reply, $matches);
        $this->assertGreaterThanOrEqual(4, count($matches[0] ?? []));
    }

    #[Test]
    public function smart_suggestions_endpoint_normalizes_titles_and_bolds_values_from_model_output(): void
    {
        $user = User::factory()->create();

        Disease::factory()->create([
            'disease_name' => 'Atrial Fibrillation',
        ]);

        config([
            'services.openrouter.api_key' => 'test-key',
            'services.google.api_key' => '',
            'services.openrouter.model' => 'primary-model',
            'services.openrouter.fallback_models' => [],
            'chatbot.read_connection' => config('database.default'),
        ]);

        $modelJson = json_encode([
            [
                'title' => 'Atrial Fibrillation Management',
                'message' => 'Atrial Fibrillation রোগীর ক্ষেত্রে today 35.4°C এবং heart_rate 110 bpm মনিটর করুন।',
                'category' => 'Condition',
                'color' => 'warning',
                'icon' => 'fa-heartbeat',
            ],
            [
                'title' => 'Medication Reminder',
                'message' => 'ওষুধ মিস না করে সময়মতো গ্রহণ করুন।',
                'category' => 'Adherence',
                'color' => 'info',
                'icon' => 'fa-pills',
            ],
            [
                'title' => 'Elevated Body Temperature',
                'message' => 'জ্বর বাড়লে বিশ্রাম নিন।',
                'category' => 'Metric Alert',
                'color' => 'danger',
                'icon' => 'fa-thermometer-half',
            ],
            [
                'title' => 'Wellness',
                'message' => 'পর্যাপ্ত পানি পান করুন।',
                'category' => 'Wellness',
                'color' => 'primary',
                'icon' => 'fa-lightbulb',
            ],
        ], JSON_UNESCAPED_UNICODE);

        Http::fake([
            'https://openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => $modelJson]],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('chatbot.smart_suggestions'))
            ->assertOk()
            ->assertJsonCount(4, 'suggestions');

        $suggestions = (array) $response->json('suggestions');
        $first = (array) ($suggestions[0] ?? []);

        $this->assertSame('এট্রিয়াল ফাইব্রিলেশন ব্যবস্থাপনা', (string) ($first['title'] ?? ''));

        $message = (string) ($first['message'] ?? '');
        $this->assertStringContainsString('এট্রিয়াল ফাইব্রিলেশন (Atrial Fibrillation)', $message);
        $this->assertStringContainsString('**heart_rate**', $message);
        $this->assertStringContainsString('**110 bpm**', $message);
    }
}
