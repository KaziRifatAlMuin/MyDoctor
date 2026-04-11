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

    private array $fallbackDivisions = [
        ['id' => 1, 'name' => 'Chattogram', 'bn_name' => 'চট্টগ্রাম'],
        ['id' => 2, 'name' => 'Rajshahi', 'bn_name' => 'রাজশাহী'],
        ['id' => 3, 'name' => 'Khulna', 'bn_name' => 'খুলনা'],
        ['id' => 4, 'name' => 'Barishal', 'bn_name' => 'বরিশাল'],
        ['id' => 5, 'name' => 'Sylhet', 'bn_name' => 'সিলেট'],
        ['id' => 6, 'name' => 'Dhaka', 'bn_name' => 'ঢাকা'],
        ['id' => 7, 'name' => 'Rangpur', 'bn_name' => 'রংপুর'],
        ['id' => 8, 'name' => 'Mymensingh', 'bn_name' => 'ময়মনসিংহ'],
    ];

    private array $fallbackDistricts = [
        ['id' => 26, 'division_id' => 6, 'name' => 'Dhaka', 'bn_name' => 'ঢাকা'],
        ['id' => 30, 'division_id' => 6, 'name' => 'Faridpur', 'bn_name' => 'ফরিদপুর'],
        ['id' => 22, 'division_id' => 1, 'name' => 'Cumilla', 'bn_name' => 'কুমিল্লা'],
        ['id' => 15, 'division_id' => 1, 'name' => 'Chattogram', 'bn_name' => 'চট্টগ্রাম'],
        ['id' => 69, 'division_id' => 2, 'name' => 'Rajshahi', 'bn_name' => 'রাজশাহী'],
        ['id' => 76, 'division_id' => 2, 'name' => 'Pabna', 'bn_name' => 'পাবনা'],
        ['id' => 47, 'division_id' => 3, 'name' => 'Khulna', 'bn_name' => 'খুলনা'],
        ['id' => 10, 'division_id' => 3, 'name' => 'Bagerhat', 'bn_name' => 'বাগেরহাট'],
        ['id' => 6, 'division_id' => 4, 'name' => 'Barishal', 'bn_name' => 'বরিশাল'],
        ['id' => 9, 'division_id' => 4, 'name' => 'Bhola', 'bn_name' => 'ভোলা'],
        ['id' => 64, 'division_id' => 5, 'name' => 'Sunamganj', 'bn_name' => 'সুনামগঞ্জ'],
        ['id' => 60, 'division_id' => 5, 'name' => 'Sylhet', 'bn_name' => 'সিলেট'],
        ['id' => 85, 'division_id' => 7, 'name' => 'Rangpur', 'bn_name' => 'রংপুর'],
        ['id' => 77, 'division_id' => 7, 'name' => 'Panchagarh', 'bn_name' => 'পঞ্চগড়'],
        ['id' => 61, 'division_id' => 8, 'name' => 'Mymensingh', 'bn_name' => 'ময়মনসিংহ'],
        ['id' => 39, 'division_id' => 8, 'name' => 'Jamalpur', 'bn_name' => 'জামালপুর'],
    ];

    private array $fallbackUpazilas = [
        ['id' => 8, 'district_id' => 26, 'name' => 'Dhanmondi', 'bn_name' => 'ধানমন্ডি'],
        ['id' => 10, 'district_id' => 26, 'name' => 'Mirpur', 'bn_name' => 'মিরপুর'],
        ['id' => 401, 'district_id' => 30, 'name' => 'Faridpur Sadar', 'bn_name' => 'ফরিদপুর সদর'],
        ['id' => 402, 'district_id' => 30, 'name' => 'Boalmari', 'bn_name' => 'বোয়ালমারী'],
        ['id' => 91, 'district_id' => 22, 'name' => 'Kotwali', 'bn_name' => 'কোতোয়ালি'],
        ['id' => 92, 'district_id' => 22, 'name' => 'Daudkandi', 'bn_name' => 'দাউদকান্দি'],
        ['id' => 194, 'district_id' => 15, 'name' => 'Pahartali', 'bn_name' => 'পাহাড়তলী'],
        ['id' => 195, 'district_id' => 15, 'name' => 'Patiya', 'bn_name' => 'পটিয়া'],
        ['id' => 501, 'district_id' => 69, 'name' => 'Rajshahi Sadar', 'bn_name' => 'রাজশাহী সদর'],
        ['id' => 502, 'district_id' => 69, 'name' => 'Paba', 'bn_name' => 'পবা'],
        ['id' => 511, 'district_id' => 76, 'name' => 'Pabna Sadar', 'bn_name' => 'পাবনা সদর'],
        ['id' => 512, 'district_id' => 76, 'name' => 'Ishwardi', 'bn_name' => 'ঈশ্বরদী'],
        ['id' => 521, 'district_id' => 47, 'name' => 'Khalishpur', 'bn_name' => 'খালিশপুর'],
        ['id' => 522, 'district_id' => 47, 'name' => 'Sonadanga', 'bn_name' => 'সোনাডাঙ্গা'],
        ['id' => 531, 'district_id' => 10, 'name' => 'Bagerhat Sadar', 'bn_name' => 'বাগেরহাট সদর'],
        ['id' => 532, 'district_id' => 10, 'name' => 'Rampal', 'bn_name' => 'রামপাল'],
        ['id' => 541, 'district_id' => 6, 'name' => 'Barishal Sadar', 'bn_name' => 'বরিশাল সদর'],
        ['id' => 542, 'district_id' => 6, 'name' => 'Bakerganj', 'bn_name' => 'বাকেরগঞ্জ'],
        ['id' => 551, 'district_id' => 9, 'name' => 'Bhola Sadar', 'bn_name' => 'ভোলা সদর'],
        ['id' => 552, 'district_id' => 9, 'name' => 'Lalmohan', 'bn_name' => 'লালমোহন'],
        ['id' => 561, 'district_id' => 64, 'name' => 'Sunamganj Sadar', 'bn_name' => 'সুনামগঞ্জ সদর'],
        ['id' => 562, 'district_id' => 64, 'name' => 'Jagannathpur', 'bn_name' => 'জগন্নাথপুর'],
        ['id' => 571, 'district_id' => 60, 'name' => 'Sylhet Sadar', 'bn_name' => 'সিলেট সদর'],
        ['id' => 572, 'district_id' => 60, 'name' => 'Beanibazar', 'bn_name' => 'বিয়ানীবাজার'],
        ['id' => 581, 'district_id' => 85, 'name' => 'Rangpur Sadar', 'bn_name' => 'রংপুর সদর'],
        ['id' => 582, 'district_id' => 85, 'name' => 'Badarganj', 'bn_name' => 'বদরগঞ্জ'],
        ['id' => 591, 'district_id' => 77, 'name' => 'Panchagarh Sadar', 'bn_name' => 'পঞ্চগড় সদর'],
        ['id' => 592, 'district_id' => 77, 'name' => 'Boda', 'bn_name' => 'বোদা'],
        ['id' => 601, 'district_id' => 61, 'name' => 'Mymensingh Sadar', 'bn_name' => 'ময়মনসিংহ সদর'],
        ['id' => 602, 'district_id' => 61, 'name' => 'Trishal', 'bn_name' => 'ত্রিশাল'],
        ['id' => 611, 'district_id' => 39, 'name' => 'Jamalpur Sadar', 'bn_name' => 'জামালপুর সদর'],
        ['id' => 612, 'district_id' => 39, 'name' => 'Melandaha', 'bn_name' => 'মেলান্দহ'],
    ];

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

        $fallbackData = $this->getFallbackData($path);
        if ($fallbackData !== null) {
            return response()->json([
                'status' => ['code' => 200, 'message' => 'ok (fallback)'],
                'data' => $fallbackData,
            ], 200);
        }

        return response()->json([
            'error' => 'Geo service unavailable',
            'data' => [],
        ], 502);
    }

    private function getFallbackData(string $path): ?array
    {
        if ($path === 'divisions') {
            return $this->fallbackDivisions;
        }

        if ($path === 'districts') {
            return $this->fallbackDistricts;
        }

        if ($path === 'upazilas') {
            return $this->fallbackUpazilas;
        }

        if (preg_match('/^districts\/(\d+)$/', $path, $matches) === 1) {
            $divisionId = (int) $matches[1];
            return array_values(array_filter(
                $this->fallbackDistricts,
                static fn (array $district): bool => (int) $district['division_id'] === $divisionId
            ));
        }

        if (preg_match('/^upazilas\/(\d+)$/', $path, $matches) === 1) {
            $districtId = (int) $matches[1];
            return array_values(array_filter(
                $this->fallbackUpazilas,
                static fn (array $upazila): bool => (int) $upazila['district_id'] === $districtId
            ));
        }

        return [];
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
