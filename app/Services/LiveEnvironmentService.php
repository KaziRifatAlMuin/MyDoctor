<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserDisease;
use App\Models\UserSymptom;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiveEnvironmentService
{
    public function forUser(User $user): array
    {
        $address = $user->address;

        if (!$address || $this->isAddressUnset($address)) {
            return [
                'available' => false,
                'reason' => 'missing_address',
                'message' => 'Set your location in profile to get live weather and air quality updates.',
            ];
        }

        $placeCandidates = $this->buildPlaceCandidates($address);
        $coords = $this->resolveCoordinates($placeCandidates);

        if (!$coords) {
            return [
                'available' => false,
                'reason' => 'geocoding_failed',
                'location_label' => $this->locationLabel($address),
                'message' => 'Could not resolve your location for live environment updates right now.',
            ];
        }

        $weather = $this->fetchWeather($coords['lat'], $coords['lon']);
        $air = $this->fetchAirQuality($coords['lat'], $coords['lon']);
        $insights = $this->buildWeatherInsights($weather);
        $llmAdvisory = $this->generateLlmAdvisory($user, $weather, $air, $insights);
        if ($llmAdvisory !== null) {
            $insights['advisory'] = $llmAdvisory;
        }

        if (!$weather && !$air) {
            return [
                'available' => false,
                'reason' => 'upstream_unavailable',
                'location_label' => $this->locationLabel($address),
                'message' => 'Live services are temporarily unavailable. Please try again shortly.',
            ];
        }

        return [
            'available' => true,
            'location_label' => $this->locationLabel($address),
            'updated_at' => now(),
            'weather' => $weather,
            'air' => $air,
            'insights' => $insights,
            'coordinates' => [
                'lat' => $coords['lat'],
                'lon' => $coords['lon'],
            ],
            'source' => 'Open-Meteo',
        ];
    }

    private function isAddressUnset(UserAddress $address): bool
    {
        $division = trim((string) ($address->division ?? ''));
        $district = trim((string) ($address->district ?? ''));
        $upazila = trim((string) ($address->upazila ?? ''));

        return ($division === '' || strcasecmp($division, 'Not set') === 0)
            && ($district === '' || strcasecmp($district, 'Not set') === 0)
            && ($upazila === '' || strcasecmp($upazila, 'Not set') === 0);
    }

    private function buildPlaceCandidates(UserAddress $address): array
    {
        $upazila = trim((string) ($address->upazila ?? ''));
        $district = trim((string) ($address->district ?? ''));
        $division = trim((string) ($address->division ?? ''));

        $sanitize = static function (string $value): string {
            return $value !== '' && strcasecmp($value, 'Not set') !== 0 ? $value : '';
        };

        $upazila = $sanitize($upazila);
        $district = $sanitize($district);
        $division = $sanitize($division);

        $candidates = [];

        // Prefer district-level resolution first to avoid ambiguous upazila names like "Sadar".
        if ($district !== '' && $division !== '') {
            $candidates[] = $district . ', ' . $division . ', Bangladesh';
            $candidates[] = $district . ' District, ' . $division . ', Bangladesh';
        }

        if ($district !== '') {
            $candidates[] = $district . ', Bangladesh';
        }

        if ($upazila !== '' && $district !== '') {
            $candidates[] = $upazila . ', ' . $district . ', Bangladesh';
        }

        if ($upazila !== '' && $division !== '') {
            $candidates[] = $upazila . ', ' . $division . ', Bangladesh';
        }

        if ($division !== '') {
            $candidates[] = $division . ', Bangladesh';
            $candidates[] = $division . ' Division, Bangladesh';
        }

        $candidates[] = 'Bangladesh';

        return array_values(array_unique(array_filter($candidates)));
    }

    private function resolveCoordinates(array $places): ?array
    {
        foreach ($places as $place) {
            $coords = $this->tryResolveCoordinates($place);
            if ($coords) {
                return $coords;
            }
        }

        // If API geocoding fails, fall back to known Bangladesh district/division centers.
        return $this->fallbackBangladeshCoordinates($places);
    }

    private function tryResolveCoordinates(string $place): ?array
    {
        $cacheKey = 'live_env_geo:' . md5(strtolower($place));

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($place) {
            $response = Http::timeout(8)
                ->retry(1, 150)
                ->get('https://geocoding-api.open-meteo.com/v1/search', [
                    'name' => $place,
                    'count' => 1,
                    'language' => 'en',
                    'format' => 'json',
                ]);

            if (!$response->successful()) {
                return null;
            }

            $result = Arr::first((array) $response->json('results', []));

            if (!$result) {
                return null;
            }

            $lat = $result['latitude'] ?? null;
            $lon = $result['longitude'] ?? null;

            if (!is_numeric($lat) || !is_numeric($lon)) {
                return null;
            }

            return [
                'lat' => (float) $lat,
                'lon' => (float) $lon,
            ];
        });
    }

    private function fallbackBangladeshCoordinates(array $places): ?array
    {
        $known = [
            'dhaka' => ['lat' => 23.8103, 'lon' => 90.4125],
            'chattogram' => ['lat' => 22.3569, 'lon' => 91.7832],
            'chittagong' => ['lat' => 22.3569, 'lon' => 91.7832],
            'khulna' => ['lat' => 22.8456, 'lon' => 89.5403],
            'rajshahi' => ['lat' => 24.3745, 'lon' => 88.6042],
            'barishal' => ['lat' => 22.7010, 'lon' => 90.3535],
            'barisal' => ['lat' => 22.7010, 'lon' => 90.3535],
            'sylhet' => ['lat' => 24.8949, 'lon' => 91.8687],
            'rangpur' => ['lat' => 25.7439, 'lon' => 89.2752],
            'mymensingh' => ['lat' => 24.7471, 'lon' => 90.4203],
            'bangladesh' => ['lat' => 23.6850, 'lon' => 90.3563],
        ];

        foreach ($places as $place) {
            $normalized = strtolower((string) $place);
            $normalized = str_replace([' district', ' division', ','], '', $normalized);

            foreach ($known as $name => $coords) {
                if (str_contains($normalized, $name)) {
                    return $coords;
                }
            }
        }

        return null;
    }

    private function fetchWeather(float $lat, float $lon): ?array
    {
        $response = Http::timeout(8)
            ->retry(1, 150)
            ->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lon,
                'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m,precipitation',
                'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_sum',
                'timezone' => 'auto',
            ]);

        if (!$response->successful()) {
            return null;
        }

        $current = (array) $response->json('current', []);

        if (!$current) {
            return null;
        }

        $code = (int) ($current['weather_code'] ?? -1);
        $daily = (array) $response->json('daily', []);
        $dailyMaxTemp = Arr::first((array) ($daily['temperature_2m_max'] ?? []));
        $dailyMinTemp = Arr::first((array) ($daily['temperature_2m_min'] ?? []));
        $dailyRainProbability = Arr::first((array) ($daily['precipitation_probability_max'] ?? []));
        $dailyRainSum = Arr::first((array) ($daily['precipitation_sum'] ?? []));

        return [
            'temperature_c' => $current['temperature_2m'] ?? null,
            'feels_like_c' => $current['apparent_temperature'] ?? null,
            'humidity' => $current['relative_humidity_2m'] ?? null,
            'wind_kmh' => $current['wind_speed_10m'] ?? null,
            'precipitation_mm' => $current['precipitation'] ?? null,
            'temp_max_c' => is_numeric($dailyMaxTemp) ? (float) $dailyMaxTemp : null,
            'temp_min_c' => is_numeric($dailyMinTemp) ? (float) $dailyMinTemp : null,
            'rain_probability_pct' => is_numeric($dailyRainProbability) ? (float) $dailyRainProbability : null,
            'rain_sum_mm' => is_numeric($dailyRainSum) ? (float) $dailyRainSum : null,
            'weather_code' => $code,
            'weather_text' => $this->weatherCodeToText($code),
            'observed_at' => $current['time'] ?? null,
        ];
    }

    private function buildWeatherInsights(?array $weather): array
    {
        if (!$weather) {
            return [
                'rain_likely' => null,
                'temperature_status' => 'unknown',
                'temperature_label' => 'Unavailable',
                'advisory' => 'Weather insight is unavailable right now.',
            ];
        }

        $rainProbability = $weather['rain_probability_pct'] ?? null;
        $rainNow = $weather['precipitation_mm'] ?? null;
        $weatherCode = (int) ($weather['weather_code'] ?? -1);
        $rainCodeMatch = in_array($weatherCode, [51, 53, 55, 56, 57, 61, 63, 65, 66, 67, 80, 81, 82, 95, 96, 99], true);
        $rainLikely = $rainCodeMatch
            || (is_numeric($rainProbability) && (float) $rainProbability >= 55)
            || (is_numeric($rainNow) && (float) $rainNow >= 0.2);

        $effectiveTemp = null;
        if (is_numeric($weather['feels_like_c'] ?? null)) {
            $effectiveTemp = (float) $weather['feels_like_c'];
        } elseif (is_numeric($weather['temperature_c'] ?? null)) {
            $effectiveTemp = (float) $weather['temperature_c'];
        }

        $status = 'unknown';
        $statusLabel = 'Unavailable';
        if ($effectiveTemp !== null) {
            if ($effectiveTemp >= 34) {
                $status = 'very_hot';
                $statusLabel = 'Very hot';
            } elseif ($effectiveTemp >= 29) {
                $status = 'hot';
                $statusLabel = 'Hot';
            } elseif ($effectiveTemp >= 19) {
                $status = 'comfortable';
                $statusLabel = 'Comfortable';
            } elseif ($effectiveTemp >= 13) {
                $status = 'cool';
                $statusLabel = 'Cool';
            } else {
                $status = 'cold';
                $statusLabel = 'Cold';
            }
        }

        $advisory = $rainLikely
            ? 'Rain is likely. Carry an umbrella if you are going out.'
            : 'No strong rain signal right now.';

        if ($status === 'very_hot' || $status === 'hot') {
            $advisory .= ' Stay hydrated and avoid direct sun in peak hours.';
        } elseif ($status === 'cold') {
            $advisory .= ' Wear warm layers if you are sensitive to cold.';
        }

        return [
            'rain_likely' => $rainLikely,
            'temperature_status' => $status,
            'temperature_label' => $statusLabel,
            'advisory' => $advisory,
        ];
    }

    private function generateLlmAdvisory(User $user, ?array $weather, ?array $air, array $insights): ?string
    {
        if (!$weather && !$air) {
            return null;
        }

        $healthContext = $this->buildHealthContext($user);
        $payload = [
            'location' => $this->locationLabel($user->address),
            'weather' => [
                'condition' => $weather['weather_text'] ?? null,
                'temperature_c' => $weather['temperature_c'] ?? null,
                'feels_like_c' => $weather['feels_like_c'] ?? null,
                'humidity' => $weather['humidity'] ?? null,
                'rain_probability_pct' => $weather['rain_probability_pct'] ?? null,
                'rain_sum_mm' => $weather['rain_sum_mm'] ?? null,
                'wind_kmh' => $weather['wind_kmh'] ?? null,
                'temp_min_c' => $weather['temp_min_c'] ?? null,
                'temp_max_c' => $weather['temp_max_c'] ?? null,
            ],
            'air_quality' => [
                'us_aqi' => $air['us_aqi'] ?? null,
                'aqi_label' => $air['us_aqi_label'] ?? null,
                'pm2_5' => $air['pm2_5'] ?? null,
                'pm10' => $air['pm10'] ?? null,
            ],
            'user_health' => $healthContext,
            'derived_flags' => [
                'rain_likely' => $insights['rain_likely'] ?? null,
                'temperature_feel' => $insights['temperature_label'] ?? null,
            ],
        ];

        $prompt = "You are MyDoctor AI. Write a short, practical health advisory in exactly 2-3 lines. "
            . "Use current weather and air data plus the user's diseases and recent symptoms. "
            . "Avoid diagnosis and panic language. Be specific and actionable for today. "
            . "Do not use markdown, bullets, titles, or numbering.\n\n"
            . "DATA:\n" . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $googleKey = (string) config('services.google.api_key', '');
        if ($googleKey !== '') {
            $text = $this->askGoogleForAdvisory($prompt, $googleKey);
            if ($text !== null) {
                return $this->normalizeAdvisoryText($text);
            }
        }

        $openRouterKey = (string) config('services.openrouter.api_key', '');
        if ($openRouterKey !== '') {
            $text = $this->askOpenRouterForAdvisory($prompt, $openRouterKey);
            if ($text !== null) {
                return $this->normalizeAdvisoryText($text);
            }
        }

        return null;
    }

    private function buildHealthContext(User $user): array
    {
        $activeDiseases = UserDisease::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'chronic', 'managed'])
            ->with('disease:id,disease_name')
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(function (UserDisease $item) {
                return [
                    'name' => $item->disease?->disease_name,
                    'status' => $item->status,
                ];
            })
            ->filter(fn(array $d) => !empty($d['name']))
            ->values()
            ->all();

        $recentSymptoms = UserSymptom::query()
            ->where('user_id', $user->id)
            ->with('symptom:id,name')
            ->latest('recorded_at')
            ->limit(8)
            ->get()
            ->map(function (UserSymptom $item) {
                return [
                    'name' => $item->symptom?->name ?? $item->symptom_name,
                    'severity' => $item->severity_level,
                    'recorded_at' => optional($item->recorded_at)->toDateString(),
                ];
            })
            ->filter(fn(array $s) => !empty($s['name']))
            ->values()
            ->all();

        return [
            'active_diseases' => $activeDiseases,
            'recent_symptoms' => $recentSymptoms,
        ];
    }

    private function askGoogleForAdvisory(string $prompt, string $apiKey): ?string
    {
        try {
            $model = (string) config('services.google.model', 'gemini-1.5-flash');
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(20)
                ->retry(1, 150)
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'maxOutputTokens' => 180,
                    ],
                ]);

            if (!$response->successful()) {
                return null;
            }

            $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
            return is_string($text) && trim($text) !== '' ? trim($text) : null;
        } catch (\Throwable $e) {
            Log::warning('LLM advisory (Google) failed', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function askOpenRouterForAdvisory(string $prompt, string $apiKey): ?string
    {
        try {
            $baseUrl = rtrim((string) config('services.openrouter.base_url', 'https://openrouter.ai/api/v1'), '/');
            $model = (string) config('services.openrouter.model', 'google/gemini-2.0-flash-001');

            $response = Http::timeout(20)
                ->retry(1, 150)
                ->withToken($apiKey)
                ->withHeaders([
                    'HTTP-Referer' => (string) config('services.openrouter.site_url', config('app.url')),
                    'X-Title' => (string) config('services.openrouter.app_name', config('app.name')),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($baseUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 180,
                ]);

            if (!$response->successful()) {
                return null;
            }

            $text = data_get($response->json(), 'choices.0.message.content');
            return is_string($text) && trim($text) !== '' ? trim($text) : null;
        } catch (\Throwable $e) {
            Log::warning('LLM advisory (OpenRouter) failed', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function normalizeAdvisoryText(string $text): string
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));
        $text = preg_replace('/^[\-*\d\.\)\s]+/m', '', $text) ?? $text;
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), fn($line) => $line !== ''));

        if (count($lines) === 0) {
            return "Weather-aware health advisory is unavailable right now.\nPlease check again in a moment for updated guidance.";
        }

        if (count($lines) > 3) {
            $lines = array_slice($lines, 0, 3);
        }

        if (count($lines) === 1) {
            // Try to split a long single paragraph into 2 lines for readability.
            $parts = preg_split('/(?<=[\.!\?])\s+/', $lines[0]) ?: [$lines[0]];
            $parts = array_values(array_filter(array_map('trim', $parts)));
            if (count($parts) >= 2) {
                $lines = array_slice($parts, 0, 3);
            } else {
                // Force 2 lines by splitting the single line into two balanced chunks.
                $words = preg_split('/\s+/', $lines[0]) ?: [];
                $words = array_values(array_filter($words, fn($w) => trim((string) $w) !== ''));
                if (count($words) >= 6) {
                    $mid = (int) ceil(count($words) / 2);
                    $first = trim(implode(' ', array_slice($words, 0, $mid)));
                    $second = trim(implode(' ', array_slice($words, $mid)));
                    $lines = [$first, $second];
                } else {
                    $lines[] = 'Monitor your symptoms and follow your prescribed care plan today.';
                }
            }
        }

        if (count($lines) === 2 && mb_strlen($lines[0] . ' ' . $lines[1]) > 240) {
            // If two lines are still too dense, split into three short lines.
            $combined = trim($lines[0] . ' ' . $lines[1]);
            $words = preg_split('/\s+/', $combined) ?: [];
            $chunk = (int) max(1, ceil(count($words) / 3));
            $lines = [
                trim(implode(' ', array_slice($words, 0, $chunk))),
                trim(implode(' ', array_slice($words, $chunk, $chunk))),
                trim(implode(' ', array_slice($words, $chunk * 2))),
            ];
            $lines = array_values(array_filter($lines, fn($line) => $line !== ''));
        }

        return implode("\n", $lines);
    }

    private function fetchAirQuality(float $lat, float $lon): ?array
    {
        $response = Http::timeout(8)
            ->retry(1, 150)
            ->get('https://air-quality-api.open-meteo.com/v1/air-quality', [
                'latitude' => $lat,
                'longitude' => $lon,
                'current' => 'pm10,pm2_5,carbon_monoxide,nitrogen_dioxide,ozone,us_aqi,european_aqi',
                'timezone' => 'auto',
            ]);

        if (!$response->successful()) {
            return null;
        }

        $current = (array) $response->json('current', []);

        if (!$current) {
            return null;
        }

        $usAqi = $current['us_aqi'] ?? null;

        return [
            'us_aqi' => $usAqi,
            'us_aqi_label' => $this->aqiLabel($usAqi),
            'pm2_5' => $current['pm2_5'] ?? null,
            'pm10' => $current['pm10'] ?? null,
            'co' => $current['carbon_monoxide'] ?? null,
            'no2' => $current['nitrogen_dioxide'] ?? null,
            'ozone' => $current['ozone'] ?? null,
            'observed_at' => $current['time'] ?? null,
        ];
    }

    private function weatherCodeToText(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear sky',
            in_array($code, [1, 2], true) => 'Partly cloudy',
            $code === 3 => 'Overcast',
            in_array($code, [45, 48], true) => 'Fog',
            in_array($code, [51, 53, 55, 56, 57], true) => 'Drizzle',
            in_array($code, [61, 63, 65, 66, 67], true) => 'Rain',
            in_array($code, [71, 73, 75, 77], true) => 'Snow',
            in_array($code, [80, 81, 82], true) => 'Rain showers',
            in_array($code, [85, 86], true) => 'Snow showers',
            in_array($code, [95, 96, 99], true) => 'Thunderstorm',
            default => 'Unknown',
        };
    }

    private function aqiLabel($aqi): string
    {
        if (!is_numeric($aqi)) {
            return 'Unavailable';
        }

        $aqi = (float) $aqi;

        return match (true) {
            $aqi <= 50 => 'Good',
            $aqi <= 100 => 'Moderate',
            $aqi <= 150 => 'Unhealthy for Sensitive Groups',
            $aqi <= 200 => 'Unhealthy',
            $aqi <= 300 => 'Very Unhealthy',
            default => 'Hazardous',
        };
    }

    private function locationLabel(UserAddress $address): string
    {
        $parts = array_values(array_filter([
            $address->upazila,
            $address->district,
            $address->division,
        ], function ($value) {
            $value = trim((string) $value);
            return $value !== '' && strcasecmp($value, 'Not set') !== 0;
        }));

        if (empty($parts)) {
            return 'Bangladesh';
        }

        return implode(', ', $parts);
    }
}
