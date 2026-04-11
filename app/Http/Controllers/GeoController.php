<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{
    private array $baseUrls = [
        'https://bdapis.vercel.app/geo/v2.0',
        'https://bdapis.com/geo/v2.0',
    ];

    public function divisions(): JsonResponse
    {
        return $this->forward('divisions');
    }

    public function districtsAll(): JsonResponse
    {
        return $this->forward('districts');
    }

    public function districtsByDivision(int $divisionId): JsonResponse
    {
        return $this->forward("districts/{$divisionId}");
    }

    public function upazilasAll(): JsonResponse
    {
        return $this->forward('upazilas');
    }

    public function upazilasByDistrict(int $districtId): JsonResponse
    {
        return $this->forward("upazilas/{$districtId}");
    }

    public function unionsByUpazila(int $upazilaId): JsonResponse
    {
        return $this->forward("unions/{$upazilaId}");
    }

    private function forward(string $path): JsonResponse
    {
        foreach ($this->baseUrls as $baseUrl) {
            try {
                $response = Http::timeout(12)->acceptJson()->get("{$baseUrl}/{$path}");

                if ($response->failed()) {
                    continue;
                }

                $payload = $response->json();
                if ($this->payloadHasNoData($payload)) {
                    continue;
                }

                return response()->json($payload, 200);
            } catch (\Throwable $e) {
                continue;
            }
        }

        return response()->json([
            'error' => 'Geo service unavailable',
            'data' => [],
        ], 502);
    }

    private function payloadHasNoData(mixed $payload): bool
    {
        if (!is_array($payload)) {
            return true;
        }

        if (isset($payload['status']['message']) && strcasecmp((string) $payload['status']['message'], 'nothing found') === 0) {
            return true;
        }

        if (!array_key_exists('data', $payload)) {
            return false;
        }

        return is_array($payload['data']) && count($payload['data']) === 0;
    }
}
