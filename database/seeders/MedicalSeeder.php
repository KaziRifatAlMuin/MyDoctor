<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\HealthMetric;
use App\Models\Symptom;
use App\Models\Medicine;
use App\Models\MedicineSchedule;
use App\Models\MedicineReminder;
use App\Models\MedicineLog;
use App\Models\User;

class MedicalSeeder extends Seeder
{
    /**
     * Run the database seeds for medical data tied to user id = 1.
     */
    public function run(): void
    {
        // Ensure user with id=1 exists. If not, create one with predictable credentials.
        if (!DB::table('users')->where('id', 1)->exists()) {
            DB::table('users')->insert([
                'id' => 1,
                'name' => 'Seed User',
                'email' => 'seed_user+1@local.test',
                'phone' => '01700000001',
                'occupation' => 'Seeded',
                'blood_group' => 'O+',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create health metrics (>= 10)
        HealthMetric::factory()->count(12)->create(['user_id' => 1]);

        // Create symptoms (>= 10)
        Symptom::factory()->count(12)->create(['user_id' => 1]);

        // Create medicines and related schedules, reminders, logs
        $medicines = Medicine::factory()->count(10)->create(['user_id' => 1]);

        foreach ($medicines as $medicine) {
            // create 2 schedules per medicine
            $schedules = MedicineSchedule::factory()->count(2)->create([
                'medicine_id' => $medicine->id,
            ]);

            // for each schedule create several reminders
            foreach ($schedules as $schedule) {
                MedicineReminder::factory()->count(8)->create([
                    'schedule_id' => $schedule->id,
                ]);
            }

            // create medicine logs for the last 30+ days with unique dates (avoid unique constraint)
            for ($d = 0; $d < 12; $d++) {
                $date = now()->subDays($d + ($medicine->id % 7))->format('Y-m-d');
                // ensure uniqueness by checking existence first
                if (!\DB::table('medicine_logs')->where(['medicine_id' => $medicine->id, 'user_id' => 1, 'date' => $date])->exists()) {
                    \App\Models\MedicineLog::factory()->create([
                        'medicine_id' => $medicine->id,
                        'user_id' => 1,
                        'date' => $date,
                    ]);
                }
            }
        }

        // Additional note: factories create necessary relations where applicable.
    }
}
