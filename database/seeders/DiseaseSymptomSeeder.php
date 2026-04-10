<?php

namespace Database\Seeders;

use App\Models\Disease;
use App\Models\Symptom;
use Illuminate\Database\Seeder;

class DiseaseSymptomSeeder extends Seeder
{
    public function run(): void
    {
        $symptomIdByName = Symptom::query()->pluck('id', 'name');
        $allSymptomIds = $symptomIdByName->values()->all();

        if (empty($allSymptomIds)) {
            $this->command?->warn('No symptoms found. Run SymptomSeeder first.');
            return;
        }

        // Top common diseases in Bangladesh + key symptom mappings.
        $map = [
            'Dengue Fever' => ['High Fever', 'Fever', 'Headache', 'Body Pain', 'Joint Pain', 'Skin Rash', 'Nausea'],
            'Typhoid' => ['Persistent Fever', 'Fever', 'Abdominal Pain', 'Loss of Appetite', 'Weakness', 'Headache'],
            'Malaria' => ['Intermittent Fever', 'Chills', 'High Fever', 'Excessive Sweating', 'Headache', 'Nausea'],
            'Tuberculosis' => ['Cough', 'Dry Cough', 'Weight Loss', 'Night Sweats', 'Fatigue', 'Chest Pain'],
            'Pneumonia' => ['Cough', 'High Fever', 'Chest Pain', 'Shortness of Breath', 'Rapid Breathing'],
            'Asthma' => ['Wheezing', 'Shortness of Breath', 'Chest Tightness', 'Dry Cough', 'Nocturnal Cough'],
            'COPD' => ['Productive Cough', 'Shortness of Breath', 'Wheezing', 'Exercise Intolerance', 'Chest Congestion'],
            'Bronchitis' => ['Cough', 'Productive Cough', 'Chest Congestion', 'Mild Fever', 'Fatigue'],
            'Sinusitis' => ['Nasal Congestion', 'Runny Nose', 'Facial Pain', 'Headache', 'Postnasal Drip'],
            'Tonsillitis' => ['Sore Throat', 'Painful Swallowing', 'Fever', 'Hoarseness', 'Throat Irritation'],
            'Measles' => ['Fever', 'High Fever', 'Skin Rash', 'Cough', 'Runny Nose', 'Red Eyes', 'Sore Throat'],
            'Chickenpox' => ['Fever', 'Skin Rash', 'Itching', 'Fatigue', 'Loss of Appetite'],
            'Diabetes Type 2' => ['Frequent Urination', 'Excessive Thirst', 'Fatigue', 'Weight Loss', 'Blurred Vision'],
            'Diabetes Type 1' => ['Frequent Urination', 'Excessive Thirst', 'Weight Loss', 'Fatigue', 'Nausea'],
            'Hypertension' => ['Headache', 'Dizziness', 'Palpitations', 'Chest Pain', 'Blurred Vision'],
            'Heart Failure' => ['Shortness of Breath', 'Swollen Feet', 'Orthopnea', 'Fatigue', 'Rapid Heart Rate'],
            'Coronary Artery Disease' => ['Chest Pain', 'Chest Pressure', 'Shortness of Breath', 'Fatigue', 'Palpitations'],
            'Stroke' => ['Facial Numbness', 'Arm Numbness', 'Leg Numbness', 'Slurred Speech', 'Balance Problems'],
            'Chronic Kidney Disease' => ['Fatigue', 'Swollen Feet', 'Reduced Urine Output', 'Nausea', 'Puffiness Around Eyes'],
            'Kidney Stones' => ['Flank Pain', 'Blood in Urine', 'Nausea', 'Vomiting', 'Urinary Urgency'],
            'Urinary Tract Infection' => ['Urinary Burning', 'Frequent Urination', 'Urinary Urgency', 'Lower Abdominal Pain', 'Fever'],
            'Anemia' => ['Fatigue', 'Weakness', 'Pale Skin', 'Dizziness', 'Rapid Heart Rate'],
            'Iron Deficiency' => ['Fatigue', 'Pale Skin', 'Hair Loss', 'Brittle Nails', 'Weakness'],
            'Thyroid Disorder' => ['Weight Gain', 'Weight Loss', 'Fatigue', 'Heat Intolerance', 'Cold Intolerance'],
            'Hypothyroidism' => ['Weight Gain', 'Fatigue', 'Dry Skin', 'Hair Loss', 'Cold Intolerance'],
            'Hyperthyroidism' => ['Weight Loss', 'Rapid Heart Rate', 'Anxiety', 'Excessive Sweating', 'Heat Intolerance'],
            'GERD' => ['Heartburn', 'Acid Reflux', 'Chest Pain', 'Sore Throat', 'Belching'],
            'Gastric Ulcer' => ['Abdominal Pain', 'Burning Stomach', 'Nausea', 'Loss of Appetite', 'Vomiting'],
            'Irritable Bowel Syndrome' => ['Abdominal Cramps', 'Bloating', 'Diarrhea', 'Constipation', 'Gas Pain'],
            'Fatty Liver' => ['Fatigue', 'Upper Abdominal Pain', 'Loss of Appetite', 'Nausea'],
            'Hepatitis B' => ['Fatigue', 'Yellow Skin', 'Dark Urine', 'Loss of Appetite', 'Abdominal Pain'],
            'Hepatitis C' => ['Fatigue', 'Yellow Skin', 'Dark Urine', 'Abdominal Pain', 'Nausea'],
            'Arthritis' => ['Joint Pain', 'Stiffness', 'Swelling', 'Morning Stiffness'],
            'Rheumatoid Arthritis' => ['Joint Pain', 'Hand Joint Stiffness', 'Morning Stiffness', 'Swelling', 'Fatigue'],
            'Osteoarthritis' => ['Knee Pain', 'Joint Pain', 'Stiffness', 'Limited Range of Motion'],
            'Gout' => ['Joint Pain', 'Swelling', 'Redness', 'Foot Pain'],
            'Migraine' => ['Headache', 'Photophobia', 'Phonophobia', 'Nausea', 'Visual Aura'],
            'Depression' => ['Low Mood', 'Loss of Interest', 'Insomnia', 'Fatigue', 'Poor Concentration'],
            'Anxiety Disorder' => ['Anxiety', 'Excessive Worry', 'Panic Feeling', 'Palpitations', 'Restlessness'],
            'Sleep Apnea' => ['Snoring', 'Breathing Pauses During Sleep', 'Daytime Sleepiness', 'Fatigue', 'Morning Headache'],
            'Obesity' => ['Weight Gain', 'Fatigue', 'Breathlessness on Exertion', 'Knee Pain', 'Sleep Disturbance'],
            'Dengue' => ['High Fever', 'Headache', 'Body Pain', 'Skin Rash', 'Nausea'],
        ];

        $diseases = Disease::query()->get();

        foreach ($diseases as $disease) {
            $mappedSymptoms = $map[$disease->disease_name] ?? [];
            $mappedIds = collect($mappedSymptoms)
                ->map(fn($name) => $symptomIdByName[$name] ?? null)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (count($mappedIds) < 3) {
                $fallbackIds = collect($allSymptomIds)->shuffle()->take(random_int(3, 7))->all();
                $mappedIds = array_values(array_unique(array_merge($mappedIds, $fallbackIds)));
            }

            $disease->symptoms()->syncWithoutDetaching($mappedIds);
        }

        $this->command?->info('Disease-symptom relations seeded successfully.');
    }
}
