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
     * Minimum 100+ per table, extra 50+ for user 1.
     * All user passwords: password
     */
    public function run(): void
    {
        // 0. Translations — must be first so back-fill works on diseases
        $this->call(TranslationSeeder::class);

        // 1. Users — no FK dependencies (50 sequential emails)
        for ($i = 1; $i <= 50; $i++) {
            User::factory()->create([
                'email' => "user{$i}@gmail.com",
                'name'  => "User {$i}",
            ]);
        }

        // 2. Diseases — no FK dependencies (120 unique diseases)
        Disease::factory(120)->create();

        // 3. UserAddresses — depends on users (120)
        UserAddress::factory(120)->create();

        // 4. Medicines — depends on users (120)
        Medicine::factory(120)->create();

        // 5. MedicineSchedules — depends on medicines (150)
        MedicineSchedule::factory(150)->create();

        // 6. MedicineReminders — depends on medicine_schedules (200)
        MedicineReminder::factory(200)->create();

        // 7. MedicineLogs — depends on medicines + users (150)
        MedicineLog::factory(150)->create();

        // 8. Symptoms — depends on users (120)
        Symptom::factory(120)->create();

        // 9. HealthMetrics — depends on users (120)
        HealthMetric::factory(120)->create();

        // 10. Environments — depends on users (120)
        Environment::factory(120)->create();

        // 11. EnvironmentMetrics — depends on environments (150)
        EnvironmentMetric::factory(150)->create();

        // 12. Uploads — depends on users (120)
        Upload::factory(120)->create();

        // 13. UserDiseases — depends on users + diseases (120)
        // Seed with unique user-disease pairs
        $userIds = User::pluck('id')->toArray();
        $diseaseIds = Disease::pluck('id')->toArray();
        $pairs = [];
        while (count($pairs) < 120) {
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

