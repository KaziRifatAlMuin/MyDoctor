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
        $healthContext = $this->buildHealthContext($user);
        $season = $this->bangladeshSeasonContext();
        $location = $this->locationLabel($address);
        $llmAdvisory = $this->generateLlmAdvisory($weather, $air, $insights, $healthContext, $season, $location);
        if ($llmAdvisory !== null) {
            $insights['advisory'] = $llmAdvisory;
        } else {
            $insights['advisory'] = $this->buildFallbackPersonalizedAdvisory($weather, $air, $insights, $healthContext, $season);
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
            'location_label' => $location,
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

    private function generateLlmAdvisory(
        ?array $weather,
        ?array $air,
        array $insights,
        array $healthContext,
        array $season,
        string $location
    ): ?string
    {
        if (!$weather && !$air) {
            return null;
        }

        $payload = [
            'location' => $location,
            'bangladesh_season' => $season,
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

        $prompt = "You are MyDoctor AI for Bangladesh users. "
            . "Write exactly 2 or 3 bullet points in Bangla for a daily health advisory. "
            . "Every bullet must be practical and actionable for today. "
            . "Use all relevant factors from data: current weather condition, rain chance, temperature feel, "
            . "Bangladesh seasonal context, and user diseases/symptoms if present. "
            . "If a disease or symptom name is in English, write it as Bangla name followed by English in bracket, e.g. এট্রিয়াল ফাইব্রিলেশন (Atrial Fibrillation). "
            . "Each bullet should connect at least two factors (example: heat + diabetes, rain + asthma, monsoon + joint pain). "
            . "Avoid diagnosis, fear language, and generic advice. "
            . "Output format rules: "
            . "(1) only bullets, (2) each line must start with '- ', (3) no heading/title, (4) no extra text before/after bullets, (5) add markdown bold for key factors (season, temperature/rain/AQI, disease/symptom name, and action words).\n\n"
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
            ->with('disease:id,disease_name,bangla_name')
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->map(function (UserDisease $item) {
                $englishName = trim((string) ($item->disease?->disease_name ?? ''));
                $banglaName = trim((string) ($item->disease?->bangla_name ?? ''));

                $name = $item->disease?->display_name;
                if ($englishName !== '' && $banglaName !== '') {
                    $name = $banglaName . ' (' . $englishName . ')';
                }

                return [
                    'name' => $name,
                    'status' => $item->status,
                ];
            })
            ->filter(fn(array $d) => !empty($d['name']))
            ->values()
            ->all();

        $recentSymptoms = UserSymptom::query()
            ->where('user_id', $user->id)
            ->with('symptom:id,name,bangla_name')
            ->latest('recorded_at')
            ->limit(8)
            ->get()
            ->map(function (UserSymptom $item) {
                return [
                    'name' => $item->symptom?->display_name ?? $item->symptom_display_name,
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
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), fn($line) => $line !== ''));

        if (count($lines) === 0) {
            return "- আজ আবহাওয়া ও বায়ুর তথ্য সম্পূর্ণ পাওয়া যায়নি, তাই বাইরে গেলে সতর্ক থাকুন।\n- আপনার বর্তমান উপসর্গ ও রোগের ওষুধ নিয়মমতো নিন এবং পানি পান করুন।";
        }

        if (count($lines) === 1) {
            $parts = preg_split('/(?<=[\.!\?।])\s+/', $lines[0]) ?: [$lines[0]];
            $parts = array_values(array_filter(array_map('trim', $parts), fn($line) => $line !== ''));
            $lines = count($parts) >= 2 ? $parts : [$lines[0], 'আজ শরীরের অবস্থা দেখে কাজের চাপ কমিয়ে বিশ্রাম নিন।'];
        }

        if (count($lines) > 3) {
            $lines = array_slice($lines, 0, 3);
        }

        $lines = array_map(function (string $line) {
            $line = trim((string) preg_replace('/^[\-*•\d\.\)\s]+/', '', $line));
            return $line === '' ? '' : '- ' . $line;
        }, $lines);

        $lines = array_values(array_filter($lines, fn($line) => $line !== ''));

        if (count($lines) < 2) {
            $lines[] = '- আজকের আবহাওয়া অনুযায়ী পানি পান, বিশ্রাম এবং নিয়মিত ওষুধে অগ্রাধিকার দিন।';
        }

        if (count($lines) > 3) {
            $lines = array_slice($lines, 0, 3);
        }

        $lines = array_map(function (string $line) {
            $map = [
                "Cushing's Syndrome" => "কুশিংস সিন্ড্রোম (Cushing's Syndrome)",
                'Atrial Fibrillation' => 'এট্রিয়াল ফাইব্রিলেশন (Atrial Fibrillation)',
                'Chickenpox' => 'চিকেনপক্স (Chickenpox)',
                'Productive Cough' => 'কফসহ কাশি (Productive Cough)',
                'Irregular Menstruation' => 'অনিয়মিত মাসিক (Irregular Menstruation)',
                'Hair Loss' => 'চুল পড়া (Hair Loss)',
                'Dry Mouth' => 'মুখ শুকানো (Dry Mouth)',
                'Spinning Sensation' => 'মাথা ঘোরা (Spinning Sensation)',
            ];

            foreach ($map as $en => $bnEn) {
                $line = preg_replace('/\b' . preg_quote($en, '/') . '\b/u', $bnEn, $line) ?? $line;
            }

            $line = preg_replace('/\b(\d+(?:\.\d+)?)\s*(bpm|°C|%)\b/u', '**$1 $2**', $line) ?? $line;
            $line = preg_replace('/\bAQI\b/u', '**AQI**', $line) ?? $line;

            return $line;
        }, $lines);

        return implode("\n", $lines);
    }

    private function bangladeshSeasonContext(): array
    {
        $month = (int) now()->month;

        return match (true) {
            in_array($month, [3, 4, 5], true) => [
                'season_en' => 'Pre-monsoon summer',
                'season_bn' => 'গ্রীষ্ম (বর্ষার আগে)',
            ],
            in_array($month, [6, 7, 8, 9], true) => [
                'season_en' => 'Monsoon',
                'season_bn' => 'বর্ষাকাল',
            ],
            in_array($month, [10, 11], true) => [
                'season_en' => 'Post-monsoon transition',
                'season_bn' => 'বর্ষা-পরবর্তী সময়',
            ],
            default => [
                'season_en' => 'Cool and dry season',
                'season_bn' => 'শীত ও শুষ্ক মৌসুম',
            ],
        };
    }

    private function buildFallbackPersonalizedAdvisory(
        ?array $weather,
        ?array $air,
        array $insights,
        array $healthContext,
        array $season
    ): string {
        $temp = is_numeric($weather['feels_like_c'] ?? null)
            ? (float) $weather['feels_like_c']
            : (is_numeric($weather['temperature_c'] ?? null) ? (float) $weather['temperature_c'] : null);
        $rainProbability = is_numeric($weather['rain_probability_pct'] ?? null) ? (float) $weather['rain_probability_pct'] : null;
        $rainLikely = (bool) ($insights['rain_likely'] ?? false);
        $aqi = is_numeric($air['us_aqi'] ?? null) ? (float) $air['us_aqi'] : null;
        $seasonBn = (string) ($season['season_bn'] ?? 'বর্তমান মৌসুম');

        $diseaseName = data_get($healthContext, 'active_diseases.0.name');
        $symptomName = data_get($healthContext, 'recent_symptoms.0.name');

        $line1 = $rainLikely
            ? "- **{$seasonBn}** সময়ে বৃষ্টির সম্ভাবনা **" . (is_null($rainProbability) ? 'উল্লেখযোগ্য' : (int) round($rainProbability) . "%") . "**। বাইরে গেলে **ছাতা** রাখুন এবং ভিজে কাপড়ে বেশি সময় থাকবেন না।"
            : "- **{$seasonBn}** সময়ে এখন তাৎক্ষণিক ভারী বৃষ্টির শক্ত ইঙ্গিত নেই, তবে হঠাৎ আবহাওয়া বদলাতে পারে তাই বাইরে গেলে **হালকা সুরক্ষা** সাথে রাখুন।";

        if ($temp !== null && $temp >= 33) {
            $line2 = "- অনুভূত তাপমাত্রা প্রায় **" . round($temp, 1) . "°C**, তাই দুপুরে সরাসরি রোদ এড়িয়ে **পানি ও ওআরএস** বাড়ান।";
        } elseif ($temp !== null && $temp <= 16) {
            $line2 = "- তাপমাত্রা তুলনামূলক কম (প্রায় **" . round($temp, 1) . "°C**), তাই ঠান্ডা-সংবেদনশীল হলে স্তরভিত্তিক পোশাক ও গরম পানি নিন।";
        } else {
            $line2 = "- তাপমাত্রা মাঝারি থাকলেও ক্লান্তি কমাতে কাজের ফাঁকে **পানি পান** ও **ছোট বিরতি** বজায় রাখুন।";
        }

        if ($diseaseName || $symptomName) {
            $healthRef = $diseaseName ? $this->medicalNameBnWithEn((string) $diseaseName) : $this->medicalNameBnWithEn((string) $symptomName);
            $airHint = ($aqi !== null && $aqi > 100)
                ? "**AQI** কিছুটা বেশি, তাই **ধুলো-ধোঁয়া এড়িয়ে মাস্ক** ব্যবহার করুন"
                : "বাইরে গেলে ধুলো-ধোঁয়া এড়িয়ে চলুন";
            $line3 = "- আপনার **{$healthRef}** বিবেচনায় {$airHint}, এবং আজ উপসর্গ বাড়লে **ওষুধের সময়সূচি** মেনে বিশ্রাম নিন।";
        } else {
            $line3 = ($aqi !== null && $aqi > 100)
                ? "- বায়ুর মান অনুকূলে নয় (**AQI " . (int) round($aqi) . "**), তাই দীর্ঘক্ষণ বাইরে ব্যায়াম না করে ঘরের ভেতর হালকা কার্যকলাপ করুন।"
                : "- আজকের আবহাওয়া ও বায়ুর অবস্থায় **হালকা ব্যায়াম**, **পর্যাপ্ত পানি** এবং **নিয়মিত ঘুম** বজায় রাখুন।";
        }

        return implode("\n", [$line1, $line2, $line3]);
    }

    private function medicalNameBnWithEn(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return 'উপসর্গ';
        }

        if (preg_match('/[\x{0980}-\x{09FF}]/u', $name)) {
            return $name;
        }

        $map = [
            "Cushing's Syndrome" => 'কুশিংস সিন্ড্রোম',
            'Atrial Fibrillation' => 'এট্রিয়াল ফাইব্রিলেশন',
            'Chickenpox' => 'চিকেনপক্স',
            'Productive Cough' => 'কফসহ কাশি',
            'Irregular Menstruation' => 'অনিয়মিত মাসিক',
            'Hair Loss' => 'চুল পড়া',
            'Dry Mouth' => 'মুখ শুকানো',
            'Spinning Sensation' => 'মাথা ঘোরা',
        ];

        foreach ($map as $en => $bn) {
            if (strcasecmp($name, $en) === 0) {
                return "{$bn} ({$en})";
            }
        }

        return $name;
    }

    private function fetchAirQuality(float $lat, float $lon): ?array
    {
        try {
            $response = Http::timeout(8)
                ->retry(1, 150)
                ->get('https://air-quality-api.open-meteo.com/v1/air-quality', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current' => 'pm10,pm2_5,carbon_monoxide,nitrogen_dioxide,ozone,us_aqi,european_aqi',
                    'timezone' => 'auto',
                ]);
        } catch (\Throwable $e) {
            Log::warning('LiveEnvironmentService: air-quality request failed', [
                'lat' => $lat,
                'lon' => $lon,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (!$response->successful()) {
            Log::debug('LiveEnvironmentService: air-quality non-success response', [
                'status' => $response->status(),
                'lat' => $lat,
                'lon' => $lon,
            ]);

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
