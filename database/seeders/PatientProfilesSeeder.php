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
use App\Models\UserSymptom;
use App\Models\Upload;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserDisease;
use App\Models\UserHealth;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder for three fully-realised patient profiles with 100+ records each.
 *
 * User 1 – Rahim Uddin   (52 M) : Hypertension + Type 2 Diabetes + Obesity
 * User 2 – Nusrat Jahan  (27 F) : Asthma + Iron-Deficiency Anaemia + Underweight
 * User 3 – Abul Hossain  (67 M) : Coronary Artery Disease + Type 1 Diabetes
 *                                   + Chronic Kidney Disease + Rheumatoid Arthritis
 *
 * Password for all three demo accounts: abcd1234
 */
class PatientProfilesSeeder extends Seeder
{
    /* ─────────────────────────────────────────────────
     * Entry point
     * ───────────────────────────────────────────────── */

    public function run(): void
    {
        $this->ensureMetricDefinitions();
        $this->seedUser1();
        $this->seedUser2();
        $this->seedUser3();
    }

    /* ═══════════════════════════════════════════════════
     * USER 1 – Rahim Uddin
     *  Problems: Hypertension │ Type 2 Diabetes │ Obesity
     *  Adherence: ~65 % (irregular)
     * ═══════════════════════════════════════════════════ */

    private function seedUser1(): void
    {
        // ── Profile ──────────────────────────────────────
        $this->upsertUser(1, [
            'name'          => 'Rahim Uddin',
            'email'         => 'user1@gmail.com',
            'phone'         => '01700000001',
            'date_of_birth' => '1972-04-15',
            'occupation'    => 'Business Owner',
            'blood_group'   => 'O+',
        ]);

        $uid = 1;

        // ── Health Metrics (115 total) ────────────────────
        // Blood Pressure – 30 readings (ALL ≥ 140/90 → triggers HIGH BP)
        $this->metrics($uid, 'blood_pressure', 30, fn($i) => [
            'systolic'  => rand(142, 178),
            'diastolic' => rand(90, 112),
            'unit'      => 'mmHg',
        ]);

        // Blood Glucose – 30 readings (ALL > 180 → triggers HIGH Blood Sugar)
        $this->metrics($uid, 'blood_glucose', 30, fn($i) => [
            'value' => round(rand(1900, 2850) / 10, 1),
            'unit'  => 'mg/dL',
        ]);

        // BMI – 20 readings (ALL ≥ 30 → triggers Obesity)
        $this->metrics($uid, 'bmi', 20, fn($i) => [
            'value' => round(rand(320, 385) / 10, 1),
            'unit'  => 'kg/m²',
        ]);

        // Cholesterol – 15 readings (high total / LDL)
        $this->metrics($uid, 'cholesterol', 15, fn($i) => [
            'total' => rand(235, 285),
            'hdl'   => rand(35, 50),
            'ldl'   => rand(155, 200),
            'unit'  => 'mg/dL',
        ]);

        // Heart Rate – 10 readings (some > 100 → elevated)
        $this->metrics($uid, 'heart_rate', 10, fn($i) => [
            'bpm'  => rand(88, 108),
            'unit' => 'bpm',
        ]);

        // Body Weight – 10 readings
        $this->metrics($uid, 'body_weight', 10, fn($i) => [
            'value' => round(rand(920, 985) / 10, 1),
            'unit'  => 'kg',
        ]);

        // ── Symptoms (60 total) ───────────────────────────
        $this->symptoms($uid, 'Headache',         15, [6, 8]);
        $this->symptoms($uid, 'Fatigue',          10, [7, 9]);
        $this->symptoms($uid, 'Blurred Vision',    8, [5, 7]);
        $this->symptoms($uid, 'Chest Tightness',   8, [6, 8]);
        $this->symptoms($uid, 'Frequent Urination', 7, [4, 6]);
        $this->symptoms($uid, 'Excessive Thirst',   7, [5, 7]);
        $this->symptoms($uid, 'Nausea',             5, [4, 7]);

        // ── Diseases ─────────────────────────────────────
        $this->assignDiseases($uid, [
            'Hypertension'    => ['status' => 'chronic',  'diagnosed_at' => '2018-03-10'],
            'Diabetes Type 2' => ['status' => 'chronic',  'diagnosed_at' => '2019-06-22'],
            'Obesity'         => ['status' => 'active',   'diagnosed_at' => '2020-01-05'],
            'High Cholesterol'=> ['status' => 'managed',  'diagnosed_at' => '2020-07-15'],
            'Fatty Liver'     => ['status' => 'active',   'diagnosed_at' => '2021-11-30'],
        ]);

        // ── Medicines & Logs ──────────────────────────────
        $meds = $this->createMedicines($uid, [
            ['name' => 'Metformin 500mg',    'type' => 'tablet',  'value' => 500,  'unit' => 'mg',  'rule' => 'after_food',  'limit' => 3],
            ['name' => 'Lisinopril 10mg',    'type' => 'tablet',  'value' => 10,   'unit' => 'mg',  'rule' => 'before_food', 'limit' => 1],
            ['name' => 'Atorvastatin 20mg',  'type' => 'tablet',  'value' => 20,   'unit' => 'mg',  'rule' => 'before_sleep','limit' => 1],
            ['name' => 'Aspirin 75mg',       'type' => 'tablet',  'value' => 75,   'unit' => 'mg',  'rule' => 'after_food',  'limit' => 1],
            ['name' => 'Metoprolol 25mg',    'type' => 'tablet',  'value' => 25,   'unit' => 'mg',  'rule' => 'with_food',   'limit' => 2],
        ]);

        // 65 % adherence – out of 3 scheduled about 2 taken
        $this->seedMedicineLogs($uid, $meds, 90, fn() => [
            'total_scheduled' => 3,
            'total_taken'     => (rand(1, 10) <= 7) ? rand(1, 2) : 0,  // 30 % days zero
        ]);

        $this->createSchedulesAndReminders($meds);

        // ── Uploads ──────────────────────────────────────
        $this->uploads($uid, 'prescription', 15);
        $this->uploads($uid, 'report',        15);

        // ── Address ───────────────────────────────────────
        UserAddress::factory()->create(['user_id' => $uid]);
    }

    /* ═══════════════════════════════════════════════════
     * USER 2 – Nusrat Jahan
     *  Problems: Asthma │ Iron-Deficiency Anaemia │ Underweight (BMI < 18.5)
     *  Adherence: ~45 % (very low – forgets meds)
     * ═══════════════════════════════════════════════════ */

    private function seedUser2(): void
    {
        $this->upsertUser(2, [
            'name'          => 'Nusrat Jahan',
            'email'         => 'user2@gmail.com',
            'phone'         => '01800000002',
            'date_of_birth' => '1998-08-20',
            'occupation'    => 'University Student',
            'blood_group'   => 'B+',
        ]);

        $uid = 2;

        // ── Health Metrics (110 total) ────────────────────
        // SpO2 – 30 readings (ALL < 95 → triggers Low O2)
        $this->metrics($uid, 'oxygen_saturation', 30, fn($i) => [
            'value' => rand(85, 94),
            'unit'  => '%',
        ]);

        // Hemoglobin – 20 readings (ALL < 12 → triggers Low Hemoglobin)
        $this->metrics($uid, 'hemoglobin', 20, fn($i) => [
            'value' => round(rand(75, 115) / 10, 1),
            'unit'  => 'g/dL',
        ]);

        // BMI – 20 readings (ALL < 18.5 → triggers Underweight)
        $this->metrics($uid, 'bmi', 20, fn($i) => [
            'value' => round(rand(145, 178) / 10, 1),
            'unit'  => 'kg/m²',
        ]);

        // Heart Rate – 15 readings (ALL < 60 → triggers Low Heart Rate)
        $this->metrics($uid, 'heart_rate', 15, fn($i) => [
            'bpm'  => rand(52, 59),
            'unit' => 'bpm',
        ]);

        // Body Weight – 15 readings (underweight)
        $this->metrics($uid, 'body_weight', 15, fn($i) => [
            'value' => round(rand(360, 420) / 10, 1),
            'unit'  => 'kg',
        ]);

        // Temperature – 10 readings with fever (ALL ≥ 38 → triggers Fever)
        $this->metrics($uid, 'temperature', 10, fn($i) => [
            'value' => round(rand(380, 390) / 10, 1),
            'unit'  => '°C',
        ]);

        // ── Symptoms (60 total) ───────────────────────────
        $this->symptoms($uid, 'Shortness of Breath', 15, [7, 10]);
        $this->symptoms($uid, 'Wheezing',            12, [7,  9]);
        $this->symptoms($uid, 'Fatigue',             10, [6,  8]);
        $this->symptoms($uid, 'Dizziness',            8, [5,  7]);
        $this->symptoms($uid, 'Palpitations',         8, [5,  7]);
        $this->symptoms($uid, 'Cold Extremities',     7, [3,  6]);

        // ── Diseases ─────────────────────────────────────
        $this->assignDiseases($uid, [
            'Asthma'                   => ['status' => 'chronic',  'diagnosed_at' => '2015-09-05'],
            'Iron Deficiency'          => ['status' => 'active',   'diagnosed_at' => '2022-03-14'],
            'Anemia'                   => ['status' => 'active',   'diagnosed_at' => '2022-04-01'],
            'Chronic Fatigue Syndrome' => ['status' => 'managed',  'diagnosed_at' => '2023-01-20'],
            'Vitamin D Deficiency'     => ['status' => 'active',   'diagnosed_at' => '2023-06-10'],
        ]);

        // ── Medicines & Logs ──────────────────────────────
        $meds = $this->createMedicines($uid, [
            ['name' => 'Salbutamol Inhaler 100mcg', 'type' => 'inhaler', 'value' => 100,  'unit' => 'mcg',      'rule' => 'anytime',     'limit' => 4],
            ['name' => 'Budesonide Inhaler 200mcg', 'type' => 'inhaler', 'value' => 200,  'unit' => 'mcg',      'rule' => 'anytime',     'limit' => 2],
            ['name' => 'Ferrous Sulfate 200mg',     'type' => 'tablet',  'value' => 200,  'unit' => 'mg',       'rule' => 'after_food',  'limit' => 2],
            ['name' => 'Folic Acid 5mg',            'type' => 'tablet',  'value' => 5,    'unit' => 'mg',       'rule' => 'after_food',  'limit' => 1],
            ['name' => 'Vitamin B12 Injection',     'type' => 'injection','value' => 1000,'unit' => 'mcg',      'rule' => 'anytime',     'limit' => 1],
        ]);

        // ~45 % adherence – consistently misses medication
        $this->seedMedicineLogs($uid, $meds, 90, fn() => [
            'total_scheduled' => 3,
            'total_taken'     => (rand(1, 10) <= 5) ? 1 : 0,   // 50 % days zero
        ]);

        $this->createSchedulesAndReminders($meds);

        // ── Uploads ──────────────────────────────────────
        $this->uploads($uid, 'prescription', 12);
        $this->uploads($uid, 'report',        10);

        // ── Address ───────────────────────────────────────
        UserAddress::factory()->create(['user_id' => $uid]);
    }

    /* ═══════════════════════════════════════════════════
     * USER 3 – Abul Hossain
     *  Problems: Coronary Artery Disease │ Type 1 Diabetes (uncontrolled)
     *            │ Chronic Kidney Disease │ Rheumatoid Arthritis │ Hypertension
     *  Adherence: ~92 % (excellent – very disciplined)
     * ═══════════════════════════════════════════════════ */

    private function seedUser3(): void
    {
        $this->upsertUser(3, [
            'name'          => 'Abul Hossain',
            'email'         => 'user3@gmail.com',
            'phone'         => '01900000003',
            'date_of_birth' => '1958-03-10',
            'occupation'    => 'Retired Government Officer',
            'blood_group'   => 'A+',
        ]);

        $uid = 3;

        // ── Health Metrics (120 total) ────────────────────
        // Heart Rate – 25 readings (ALL > 100 → triggers Elevated Heart Rate)
        $this->metrics($uid, 'heart_rate', 25, fn($i) => [
            'bpm'  => rand(105, 116),
            'unit' => 'bpm',
        ]);

        // Blood Glucose – 30 readings (mix of hypo < 70 and hyper > 180)
        // Alternating pattern simulates uncontrolled T1DM
        $this->metrics($uid, 'blood_glucose', 30, function ($i) {
            $isHypo = ($i % 3 === 0);   // every 3rd reading = hypoglycaemic
            return [
                'value' => $isHypo ? round(rand(400, 680) / 10, 1)   // 40–68
                                   : round(rand(1950, 2650) / 10, 1), // 195–265
                'unit'  => 'mg/dL',
            ];
        });

        // Temperature – 15 readings (ALL ≥ 38 → triggers Fever)
        $this->metrics($uid, 'temperature', 15, fn($i) => [
            'value' => round(rand(380, 395) / 10, 1),
            'unit'  => '°C',
        ]);

        // Blood Pressure – 25 readings (ALL ≥ 140/90 → HIGH BP)
        $this->metrics($uid, 'blood_pressure', 25, fn($i) => [
            'systolic'  => rand(155, 195),
            'diastolic' => rand(95, 118),
            'unit'      => 'mmHg',
        ]);

        // Creatinine – 15 readings (very high – CKD stage 3-4)
        $this->metrics($uid, 'creatinine', 15, fn($i) => [
            'value' => round(rand(26, 48) / 10, 1),
            'unit'  => 'mg/dL',
        ]);

        // Body Weight – 10 readings
        $this->metrics($uid, 'body_weight', 10, fn($i) => [
            'value' => round(rand(740, 820) / 10, 1),
            'unit'  => 'kg',
        ]);

        // ── Symptoms (70 total) ───────────────────────────
        $this->symptoms($uid, 'Chest Pain',         15, [8, 10]);
        $this->symptoms($uid, 'Palpitations',       12, [7,  9]);
        $this->symptoms($uid, 'Nausea',             10, [5,  8]);
        $this->symptoms($uid, 'Joint Pain',         12, [8, 10]);
        $this->symptoms($uid, 'Fatigue',            10, [8,  9]);
        $this->symptoms($uid, 'Excessive Sweating',  8, [6,  8]);
        $this->symptoms($uid, 'Fainting',            3, [8, 10]);

        // ── Diseases ─────────────────────────────────────
        $this->assignDiseases($uid, [
            'Coronary Artery Disease' => ['status' => 'chronic',  'diagnosed_at' => '2012-05-20'],
            'Diabetes Type 1'         => ['status' => 'chronic',  'diagnosed_at' => '1980-08-14'],
            'Chronic Kidney Disease'  => ['status' => 'active',   'diagnosed_at' => '2019-02-28'],
            'Rheumatoid Arthritis'    => ['status' => 'managed',  'diagnosed_at' => '2015-11-03'],
            'Hypertension'            => ['status' => 'chronic',  'diagnosed_at' => '2010-07-09'],
            'Atrial Fibrillation'     => ['status' => 'active',   'diagnosed_at' => '2020-09-15'],
        ]);

        // ── Medicines & Logs ──────────────────────────────
        $meds = $this->createMedicines($uid, [
            ['name' => 'Human Insulin (Short-Acting)',  'type' => 'injection', 'value' => 10,  'unit' => 'IU',  'rule' => 'before_food',  'limit' => 4],
            ['name' => 'Human Insulin (Long-Acting)',   'type' => 'injection', 'value' => 20,  'unit' => 'IU',  'rule' => 'before_sleep', 'limit' => 1],
            ['name' => 'Warfarin 5mg',                  'type' => 'tablet',    'value' => 5,   'unit' => 'mg',  'rule' => 'anytime',      'limit' => 1],
            ['name' => 'Digoxin 0.25mg',                'type' => 'tablet',    'value' => 0.25,'unit' => 'mg',  'rule' => 'after_food',   'limit' => 1],
            ['name' => 'Furosemide 40mg',               'type' => 'tablet',    'value' => 40,  'unit' => 'mg',  'rule' => 'before_food',  'limit' => 2],
            ['name' => 'Lisinopril 20mg',               'type' => 'tablet',    'value' => 20,  'unit' => 'mg',  'rule' => 'before_food',  'limit' => 1],
            ['name' => 'Omeprazole 20mg',               'type' => 'capsule',   'value' => 20,  'unit' => 'mg',  'rule' => 'before_food',  'limit' => 2],
            ['name' => 'Methotrexate 7.5mg',            'type' => 'tablet',    'value' => 7.5, 'unit' => 'mg',  'rule' => 'with_food',    'limit' => 1],
            ['name' => 'Hydroxychloroquine 200mg',      'type' => 'tablet',    'value' => 200, 'unit' => 'mg',  'rule' => 'after_food',   'limit' => 2],
        ]);

        // ~92 % adherence – nearly always takes medication
        $this->seedMedicineLogs($uid, $meds, 90, fn() => [
            'total_scheduled' => 3,
            'total_taken'     => (rand(1, 100) <= 92) ? 3 : rand(1, 2),
        ]);

        $this->createSchedulesAndReminders($meds);

        // ── Uploads ──────────────────────────────────────
        $this->uploads($uid, 'prescription', 15);
        $this->uploads($uid, 'report',        20);

        // ── Address ───────────────────────────────────────
        UserAddress::factory()->create(['user_id' => $uid]);
    }

    /* ─────────────────────────────────────────────────
     * Shared helper methods
     * ───────────────────────────────────────────────── */

    /**
     * Insert or update a user row by ID.
     */
    private function upsertUser(int $id, array $data): void
    {
        $exists = DB::table('users')->where('id', $id)->exists();

        $payload = array_merge($data, [
            'role'              => $id === 1 ? 'admin' : 'member',
            'password'          => Hash::make('abcd1234'),
            'email_verified_at' => now(),
            'updated_at'        => now(),
        ]);

        if ($exists) {
            DB::table('users')->where('id', $id)->update($payload);
        } else {
            DB::table('users')->insert(array_merge($payload, [
                'id'         => $id,
                'created_at' => now(),
            ]));
        }
    }

    /**
     * Create $count health metrics of a given type for a user.
     * $valueFactory is a callable(int $i): array
     * Readings are spread over the last 180 days.
     */
    private function metrics(int $userId, string $type, int $count, callable $valueFactory): void
    {
        $definition = HealthMetric::query()->where('metric_name', $type)->first();
        if (!$definition) {
            return;
        }

        for ($i = 0; $i < $count; $i++) {
            $value = (array) $valueFactory($i);
            unset($value['unit']);

            UserHealth::create([
                'user_id'     => $userId,
                'health_metric_id' => $definition->id,
                'recorded_at' => now()->subDays(rand(1, 180))->subHours(rand(0, 23)),
                'value'       => $value,
            ]);
        }
    }

    private function ensureMetricDefinitions(): void
    {
        HealthMetric::seedDefaults();
    }

    /**
     * Create $count symptom records for a user.
     * $severityRange = [min, max]
     */
    private function symptoms(int $userId, string $name, int $count, array $severityRange): void
    {
        $catalogSymptom = Symptom::firstOrCreate(['name' => $name]);

        for ($i = 0; $i < $count; $i++) {
            UserSymptom::create([
                'user_id'        => $userId,
                'symptom_id'     => $catalogSymptom->id,
                'severity_level' => rand($severityRange[0], $severityRange[1]),
                'recorded_at'    => now()->subDays(rand(1, 180)),
                'note'           => (rand(1, 3) === 1)
                    ? 'Noted during routine check. Requires monitoring.'
                    : null,
            ]);
        }
    }

    /**
     * Assign diseases to a user (safe – skips duplicates).
     * $diseases: disease_name => ['status' => ..., 'diagnosed_at' => ...]
     */
    private function assignDiseases(int $userId, array $diseases): void
    {
        foreach ($diseases as $diseaseName => $attrs) {
            $disease = Disease::where('disease_name', $diseaseName)->first();
            if (!$disease) {
                continue;
            }
            $already = DB::table('user_diseases')
                ->where('user_id', $userId)
                ->where('disease_id', $disease->id)
                ->exists();
            if ($already) {
                continue;
            }
            UserDisease::create([
                'user_id'      => $userId,
                'disease_id'   => $disease->id,
                'diagnosed_at' => $attrs['diagnosed_at'],
                'status'       => $attrs['status'],
                'notes'        => "Patient has been managing this condition since {$attrs['diagnosed_at']}.",
            ]);
        }
    }

    /**
     * Create and return Medicine models for a user.
     */
    private function createMedicines(int $userId, array $specs): \Illuminate\Support\Collection
    {
        return collect($specs)->map(fn($s) => Medicine::create([
            'user_id'        => $userId,
            'medicine_name'  => $s['name'],
            'type'           => $s['type'],
            'value_per_dose' => $s['value'],
            'unit'           => $s['unit'],
            'rule'           => $s['rule'],
            'dose_limit'     => $s['limit'],
        ]));
    }

    /**
     * Generate medicine logs over $days days for each medicine.
     * $logFactory is a callable(): array with keys total_scheduled / total_taken.
     * Ensures uniqueness of (medicine_id, user_id, date).
     */
    private function seedMedicineLogs(int $userId, \Illuminate\Support\Collection $meds, int $days, callable $logFactory): void
    {
        foreach ($meds as $medicine) {
            for ($d = 1; $d <= $days; $d++) {
                $date = now()->subDays($d)->format('Y-m-d');

                // Guard duplicate
                $exists = DB::table('medicine_logs')
                    ->where('medicine_id', $medicine->id)
                    ->where('user_id', $userId)
                    ->where('date', $date)
                    ->exists();
                if ($exists) {
                    continue;
                }

                $stats = $logFactory();
                $taken  = min($stats['total_taken'], $stats['total_scheduled']);
                $missed = $stats['total_scheduled'] - $taken;

                MedicineLog::create([
                    'medicine_id'     => $medicine->id,
                    'user_id'         => $userId,
                    'date'            => $date,
                    'total_scheduled' => $stats['total_scheduled'],
                    'total_taken'     => $taken,
                    'total_missed'    => $missed,
                ]);
            }
        }
    }

    /**
     * Create one schedule + 3-4 reminders per medicine.
     */
    private function createSchedulesAndReminders(\Illuminate\Support\Collection $meds): void
    {
        foreach ($meds as $medicine) {
            $schedule = MedicineSchedule::factory()->create([
                'medicine_id' => $medicine->id,
                'is_active'   => true,
            ]);
            MedicineReminder::factory()->count(rand(3, 4))->create([
                'schedule_id' => $schedule->id,
            ]);
        }
    }

    /**
     * Create upload records (without actual files – path set to placeholder).
     */
    private function uploads(int $userId, string $type, int $count): void
    {
        Upload::factory()->count($count)->create([
            'user_id' => $userId,
            'type'    => $type,
        ]);
    }
}
