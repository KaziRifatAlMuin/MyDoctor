<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddressFactory extends Factory
{
    public function definition(): array
    {
        $districts = ['Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 'Sylhet', 'Barishal', 'Rangpur', 'Mymensingh'];
        $upazilas  = ['Mirpur', 'Gulshan', 'Dhanmondi', 'Uttara', 'Mohakhali', 'Wari', 'Motijheel', 'Banani'];

        return [
            'user_id'  => User::inRandomOrder()->first()?->id ?? User::factory(),
            'district' => fake()->randomElement($districts),
            'upazila'  => fake()->randomElement($upazilas),
            'street'   => fake()->streetName(),
            'house'    => fake()->buildingNumber(),
        ];
    }
}
