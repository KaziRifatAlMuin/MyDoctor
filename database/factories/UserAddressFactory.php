<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddressFactory extends Factory
{
    public function definition(): array
    {
        $divisions = [
            ['id' => 1, 'en' => 'Chattogram', 'bn' => 'চট্টগ্রাম'],
            ['id' => 2, 'en' => 'Rajshahi', 'bn' => 'রাজশাহী'],
            ['id' => 3, 'en' => 'Khulna', 'bn' => 'খুলনা'],
            ['id' => 4, 'en' => 'Barishal', 'bn' => 'বরিশাল'],
            ['id' => 5, 'en' => 'Sylhet', 'bn' => 'সিলেট'],
            ['id' => 6, 'en' => 'Dhaka', 'bn' => 'ঢাকা'],
            ['id' => 7, 'en' => 'Rangpur', 'bn' => 'রংপুর'],
            ['id' => 8, 'en' => 'Mymensingh', 'bn' => 'ময়মনসিংহ'],
        ];
        $pickedDivision = fake()->randomElement($divisions);

        $districts = [
            ['id' => 26, 'en' => 'Dhaka', 'bn' => 'ঢাকা'],
            ['id' => 22, 'en' => 'Cumilla', 'bn' => 'কুমিল্লা'],
            ['id' => 10, 'en' => 'Bagerhat', 'bn' => 'বাগেরহাট'],
            ['id' => 64, 'en' => 'Sunamganj', 'bn' => 'সুনামগঞ্জ'],
        ];
        $pickedDistrict = fake()->randomElement($districts);

        $upazilas = [
            ['id' => 8, 'en' => 'Dhanmondi', 'bn' => 'ধানমন্ডি'],
            ['id' => 91, 'en' => 'Kotwali', 'bn' => 'কোতোয়ালি'],
            ['id' => 194, 'en' => 'Sadar', 'bn' => 'সদর'],
            ['id' => 312, 'en' => 'Jagannathpur', 'bn' => 'জগন্নাথপুর'],
        ];
        $pickedUpazila = fake()->randomElement($upazilas);

        return [
            'user_id'  => User::factory(),
            'division_id' => $pickedDivision['id'],
            'division' => $pickedDivision['en'],
            'division_bn' => $pickedDivision['bn'],
            'district_id' => $pickedDistrict['id'],
            'district' => $pickedDistrict['en'],
            'district_bn' => $pickedDistrict['bn'],
            'upazila_id' => $pickedUpazila['id'],
            'upazila'  => $pickedUpazila['en'],
            'upazila_bn'  => $pickedUpazila['bn'],
            'street'   => fake()->streetName(),
            'house'    => fake()->buildingNumber(),
        ];
    }
}
