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
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * FK-safe insertion order is maintained.
     */
    public function run(): void
    {
        // 1. Users — no FK dependencies
        User::factory(50)->create();

        // 2. Diseases — no FK dependencies
        Disease::factory(50)->create();

        // 3. UserAddresses — depends on users
        UserAddress::factory(50)->create();

        // 4. Medicines — depends on users
        Medicine::factory(50)->create();

        // 5. MedicineSchedules — depends on medicines
        MedicineSchedule::factory(50)->create();

        // 6. MedicineReminders — depends on medicine_schedules
        MedicineReminder::factory(50)->create();

        // 7. MedicineLogs — depends on medicines + users
        MedicineLog::factory(50)->create();

        // 8. Symptoms — depends on users
        Symptom::factory(50)->create();

        // 9. HealthMetrics — depends on users
        HealthMetric::factory(50)->create();

        // 10. Environments — depends on users
        Environment::factory(50)->create();

        // 11. EnvironmentMetrics — depends on environments
        EnvironmentMetric::factory(50)->create();
    }
}

