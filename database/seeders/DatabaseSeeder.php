<?php

namespace Database\Seeders;

use App\Models\Disease;
use App\Models\Environment;
use App\Models\EnvironmentMetric;
use App\Models\HealthMetric;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\MedicineReminder;
use App\Models\MedicineSchedule;
use App\Models\Symptom;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserDisease;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\MedicalSeeder;
use Database\Seeders\TranslationSeeder;
use Database\Seeders\PatientProfilesSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * FK-safe insertion order is maintained.
     * 50 users with associated data.
     * All user passwords: abcd1234
     */
    public function run(): void
    {
        // 0. Translations — must be first so back-fill works on diseases
        $this->call(TranslationSeeder::class);

        // 1. Diseases — create from config before users (to avoid duplicates)
        $diseaseNames = array_keys(config('health.diseases'));
        foreach ($diseaseNames as $name) {
            Disease::firstOrCreate(
                ['disease_name' => $name],
                [
                    'disease_name_bn' => config('health.diseases')[$name],
                    'description' => fake()->paragraph(2),
                ]
            );
        }

        // 2. Users — no FK dependencies (50 sequential emails with random names)
        for ($i = 1; $i <= 50; $i++) {
            User::factory()->create([
                'email' => "user{$i}@gmail.com",
                'role' => $i === 1 ? 'admin' : 'member', // Make first user admin
            ]);
        }


        // 3. UserAddresses — depends on users (50, one per user)
        UserAddress::factory(50)->create();

        // 4. Medicines — depends on users (50)
        Medicine::factory(50)->create();

        // 5. MedicineSchedules — depends on medicines (60)
        MedicineSchedule::factory(60)->create();

        // 6. MedicineReminders — depends on medicine_schedules (80)
        MedicineReminder::factory(80)->create();

        // 7. MedicineLogs — depends on medicines + users (60)
        MedicineLog::factory(60)->create();

        // 8. Symptoms — depends on users (50)
        Symptom::factory(50)->create();

        // 9. HealthMetrics — depends on users (50)
        HealthMetric::factory(50)->create();

        // 10. Environments — depends on users (50)
        Environment::factory(50)->create();

        // 11. EnvironmentMetrics — depends on environments (60)
        EnvironmentMetric::factory(60)->create();

        // 12. Uploads — depends on users (50)
        Upload::factory(50)->create();

        // 13. UserDiseases — depends on users + diseases (50)
        // Seed with unique user-disease pairs
        $userIds = User::pluck('id')->toArray();
        $diseaseIds = Disease::pluck('id')->toArray();
        $pairs = [];
        while (count($pairs) < 50) {
            $uid = $userIds[array_rand($userIds)];
            $did = $diseaseIds[array_rand($diseaseIds)];
            $key = "$uid-$did";
            if (!isset($pairs[$key])) {
                $pairs[$key] = true;
                UserDisease::factory()->create([
                    'user_id' => $uid,
                    'disease_id' => $did,
                ]);
            }
        }

        // 14. Medical seeding for user id=1 (targeted, 50+ per table for user 1)
        $this->call(MedicalSeeder::class);

        // 15. Rich patient profiles for users 1–3 (100+ records each, distinct conditions)
        $this->call(PatientProfilesSeeder::class);
    }
}

