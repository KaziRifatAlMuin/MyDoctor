<?php

namespace Database\Seeders;

use App\Models\Symptom;
use App\Models\Translation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SymptomSeeder extends Seeder
{
    public function run(): void
    {
        $baseSymptoms = array_keys(config('health.symptoms', []));

        $extraSymptoms = [
            'Mild Fever', 'Low-grade Fever', 'Intermittent Fever', 'Persistent Fever', 'Fever with Rash',
            'General Malaise', 'Body Heaviness', 'Exhaustion', 'Poor Stamina', 'Post-exertional Fatigue',
            'Lightheadedness', 'Vertigo', 'Spinning Sensation', 'Near Fainting', 'Blackout Episodes',
            'Photophobia', 'Phonophobia', 'Scalp Tenderness', 'Sinus Headache', 'Frontal Headache',
            'Occipital Headache', 'Neck Stiffness', 'Neck Spasm', 'Facial Pain', 'Facial Numbness',
            'Dry Eyes', 'Watery Eyes', 'Red Eyes', 'Eye Redness', 'Eye Discharge',
            'Eyelid Swelling', 'Itchy Eyes', 'Eye Irritation', 'Visual Aura', 'Floaters',
            'Ear Fullness', 'Ear Discharge', 'Reduced Hearing', 'Sore Ears', 'Blocked Ears',
            'Throat Irritation', 'Dry Throat', 'Scratchy Throat', 'Tonsil Swelling', 'Bad Breath',
            'Loss of Smell', 'Reduced Smell', 'Loss of Taste', 'Reduced Taste', 'Postnasal Drip',
            'Nasal Itching', 'Facial Congestion', 'Chest Congestion', 'Productive Cough', 'Dry Cough',
            'Nocturnal Cough', 'Cough with Blood', 'Breathlessness on Exertion', 'Breathlessness at Rest', 'Painful Breathing',
            'Rapid Pulse', 'Irregular Heartbeat', 'Skipped Heartbeats', 'Orthopnea', 'Pitting Edema',
            'Hand Swelling', 'Ankle Swelling', 'Foot Numbness', 'Burning Feet', 'Cold Sweats',
            'Abdominal Cramps', 'Upper Abdominal Pain', 'Lower Abdominal Pain', 'Right Upper Quadrant Pain', 'Left Upper Quadrant Pain',
            'Epigastric Pain', 'Indigestion', 'Early Satiety', 'Poor Digestion', 'Sour Belching',
            'Acid Reflux', 'Burning Stomach', 'Mucus in Stool', 'Loose Stools', 'Hard Stools',
            'Urgency to Defecate', 'Tenesmus', 'Painful Defecation', 'Rectal Bleeding', 'Dark Stools',
            'Pale Stools', 'Frequent Bowel Movements', 'Infrequent Bowel Movements', 'Bitter Taste', 'Dry Lips',
            'Frequent Night Urination', 'Urinary Urgency', 'Urinary Hesitancy', 'Weak Urine Stream', 'Incomplete Bladder Emptying',
            'Urinary Incontinence', 'Foul-smelling Urine', 'Cloudy Urine', 'Flank Pain', 'Lower Back Ache',
            'Pelvic Pressure', 'Lower Pelvic Cramps', 'Menstrual Cramps', 'Heavy Menstrual Bleeding', 'Irregular Menstruation',
            'Missed Periods', 'Painful Intercourse', 'Vaginal Discharge', 'Vaginal Itching', 'Breast Tenderness',
            'Nipple Discharge', 'Erectile Dysfunction', 'Reduced Libido', 'Testicular Pain', 'Scrotal Swelling',
            'Muscle Weakness', 'Muscle Stiffness', 'Muscle Twitching', 'Calf Pain', 'Thigh Pain',
            'Shin Pain', 'Heel Pain', 'Foot Pain', 'Toe Pain', 'Finger Joint Pain',
            'Hand Joint Stiffness', 'Morning Stiffness', 'Limited Range of Motion', 'Back Spasm', 'Lower Back Stiffness',
            'Upper Back Pain', 'Shoulder Stiffness', 'Arm Numbness', 'Hand Numbness', 'Leg Numbness',
            'Pins and Needles', 'Burning Sensation', 'Electric Shock Sensation', 'Restless Legs', 'Unsteady Gait',
            'Sleep Disturbance', 'Daytime Sleepiness', 'Frequent Awakening', 'Snoring', 'Breathing Pauses During Sleep',
            'Poor Sleep Quality', 'Vivid Dreams', 'Nightmares', 'Teeth Grinding', 'Jaw Clenching',
            'Dry Cough at Night', 'Exercise Intolerance', 'Dehydration', 'Sun Sensitivity', 'Heat Intolerance',
            'Cold Intolerance', 'Hand Tremor', 'Shakiness', 'Speech Difficulty', 'Word Finding Difficulty',
            'Slow Speech', 'Slurred Speech', 'Confused Speech', 'Reduced Alertness', 'Difficulty Concentrating',
            'Irritable Mood', 'Low Mood', 'Hopelessness', 'Feeling Nervous', 'Excessive Worry',
            'Panic Feeling', 'Social Withdrawal', 'Loss of Interest', 'Emotional Numbness', 'Crying Spells',
            'Skin Dryness', 'Skin Peeling', 'Skin Redness', 'Skin Warmth', 'Skin Tightness',
            'Hives', 'Blisters', 'Pustules', 'Acne Breakouts', 'Facial Flushing',
            'Dark Patches on Skin', 'Pale Lips', 'Brittle Nails', 'Nail Discoloration', 'Hair Thinning',
            'Scalp Itching', 'Scalp Flaking', 'Delayed Wound Healing', 'Easy Bleeding', 'Frequent Infections',
            'Frequent Bruising', 'Swollen Glands', 'Mouth Dryness', 'Tongue Pain', 'Tongue Ulcer',
            'Mouth Burning', 'Loss of Voice', 'Voice Fatigue', 'Persistent Hoarseness', 'Difficulty Speaking',
            'Shallow Breathing', 'Chest Heaviness', 'Pleural Pain', 'Pain on Coughing', 'Exercise-induced Wheezing',
            'Post-meal Sleepiness', 'Post-meal Bloating', 'Food Intolerance', 'Milk Intolerance', 'Gluten Intolerance',
            'Motion Sickness', 'Travel Sickness', 'Nausea After Meals', 'Morning Nausea', 'Dry Heaving',
            'Vomiting After Meals', 'Persistent Vomiting', 'Excessive Hunger', 'Increased Appetite', 'Reduced Urine Output',
            'Excessive Urine Output', 'Puffiness Around Eyes', 'Facial Swelling', 'Neck Swelling', 'Throat Swelling',
            'Difficulty Opening Mouth', 'Painful Swallowing', 'Choking Sensation', 'Lump in Throat', 'Burning Chest',
            'Heart Fluttering', 'Chest Pressure', 'Radiating Chest Pain', 'Left Arm Pain', 'Jaw Tightness',
            'Right Shoulder Pain', 'Generalized Itching', 'Anal Itching', 'Rash on Face', 'Rash on Trunk',
            'Rash on Limbs', 'Patchy Hair Loss', 'Dry Cough with Fever', 'Persistent Sneezing', 'Chronic Runny Nose',
        ];

        $allSymptoms = collect(array_merge($baseSymptoms, $extraSymptoms))
            ->map(fn($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->take(235)
            ->values();

        foreach ($allSymptoms as $symptomName) {
            $bangla = Translation::banglaFor(Translation::TYPE_SYMPTOM, $symptomName, $symptomName);

            $payload = [];
            if (Schema::hasColumn('symptoms', 'bangla_name')) {
                $payload['bangla_name'] = $bangla;
            }
            if (Schema::hasColumn('symptoms', 'name_bn')) {
                $payload['name_bn'] = $bangla;
            }

            Symptom::updateOrCreate(
                ['name' => $symptomName],
                $payload
            );
        }

        $this->command?->info('Symptoms seeded: ' . $allSymptoms->count());
    }
}
