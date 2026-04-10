<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\HealthMetric;
use App\Models\UserHealth;
use App\Models\Symptom;
use App\Models\UserSymptom;
use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\MedicineReminder;
use App\Models\MedicineLog;
use App\Models\Upload;
use App\Models\UserDisease;
use App\Models\Disease;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Environment;
use App\Models\EnvironmentMetric;

class MedicalSeeder extends Seeder
{
    /**
     * Run the database seeds for medical data tied to user id = 1.
     * Minimum 50+ records per table for user 1.
     */
    public function run(): void
    {
        $this->ensureMetricDefinitions();

        // Ensure user with id=1 exists with password "abcd1234"
        if (!DB::table('users')->where('id', 1)->exists()) {
            DB::table('users')->insert([
                'id'                => 1,
                'name'              => 'Test User',
                'email'             => 'test@mydoctor.com',
                'phone'             => '01700000001',
                'date_of_birth'     => '1995-06-15',
                'role'              => 'admin',
                'occupation'        => 'Software Engineer',
                'blood_group'       => 'O+',
                'email_verified_at' => now(),
                'password'          => Hash::make('abcd1234'),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        } else {
            // Update password for user 1 to "abcd1234"
            DB::table('users')->where('id', 1)->update([
                'password' => Hash::make('abcd1234'),
            ]);
        }

        // Health Metrics — 60 records for user 1
        UserHealth::factory()->count(60)->create(['user_id' => 1]);

        // Symptoms — 55 records for user 1
        UserSymptom::factory()->count(55)->create(['user_id' => 1]);

        // User Addresses — 3 for user 1
        UserAddress::factory()->count(3)->create(['user_id' => 1]);

        // Medicines — 15 medicines for user 1
        $medicines = Medicine::factory()->count(15)->create(['user_id' => 1]);

        // Schedules — 2-3 per medicine (~35-45 schedules)
        $allSchedules = collect();
        foreach ($medicines as $medicine) {
            $scheduleCount = fake()->numberBetween(2, 3);
            $schedules = MedicineSchedule::factory()->count($scheduleCount)->create([
                'medicine_id' => $medicine->id,
            ]);
            $allSchedules = $allSchedules->merge($schedules);
        }

        // Reminders — 3-5 per schedule (~100-225 reminders)
        foreach ($allSchedules as $schedule) {
            MedicineReminder::factory()->count(fake()->numberBetween(3, 5))->create([
                'schedule_id' => $schedule->id,
            ]);
        }

        // Medicine Logs — unique date per medicine for user 1 (~55+ total)
        foreach ($medicines as $medicine) {
            $daysUsed = [];
            for ($d = 0; $d < 5; $d++) {
                $date = now()->subDays($d + ($medicine->id % 10))->format('Y-m-d');
                if (!in_array($date, $daysUsed) && !DB::table('medicine_logs')
                    ->where(['medicine_id' => $medicine->id, 'user_id' => 1, 'date' => $date])->exists()) {
                    $daysUsed[] = $date;
                    MedicineLog::factory()->create([
                        'medicine_id' => $medicine->id,
                        'user_id'     => 1,
                        'date'        => $date,
                    ]);
                }
            }
        }

        // Uploads — 30 prescriptions + 25 reports for user 1
        Upload::factory()->count(30)->create([
            'user_id' => 1,
            'type'    => 'prescription',
        ]);
        Upload::factory()->count(25)->create([
            'user_id' => 1,
            'type'    => 'report',
        ]);

        // User Diseases — assign 8-12 diseases to user 1
        $diseaseIds = Disease::pluck('id')->shuffle()->take(10)->toArray();
        foreach ($diseaseIds as $diseaseId) {
            if (!DB::table('user_diseases')->where(['user_id' => 1, 'disease_id' => $diseaseId])->exists()) {
                UserDisease::factory()->create([
                    'user_id'    => 1,
                    'disease_id' => $diseaseId,
                ]);
            }
        }

        // Environments — 10 for user 1
        $environments = Environment::factory()->count(10)->create(['user_id' => 1]);

        // Environment Metrics — 3 per environment
        foreach ($environments as $env) {
            EnvironmentMetric::factory()->count(3)->create([
                'environment_id' => $env->id,
            ]);
        }
    }

    private function ensureMetricDefinitions(): void
    {
        if (HealthMetric::query()->exists()) {
            return;
        }

        foreach (config('health.metric_types', []) as $metricName => $cfg) {
            HealthMetric::query()->create([
                'metric_name' => $metricName,
                'fields' => array_values((array) ($cfg['fields'] ?? [])),
            ]);
        }
    }
}
