<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UploadFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['prescription', 'report']);

        $prescriptionTitles = [
            'General Checkup Prescription',
            'Blood Pressure Medication',
            'Diabetes Management Prescription',
            'Allergy Treatment Prescription',
            'Antibiotic Course Prescription',
            'Pain Management Prescription',
            'Thyroid Medication Prescription',
            'Heart Health Prescription',
            'Respiratory Treatment',
            'Vitamin Supplement Prescription',
            'Follow-up Visit Prescription',
            'Dermatology Prescription',
            'Eye Care Prescription',
            'Gastric Treatment Prescription',
            'Post-Surgery Medication',
        ];

        $reportTitles = [
            'Complete Blood Count (CBC)',
            'Lipid Profile Report',
            'Liver Function Test',
            'Kidney Function Test',
            'Thyroid Function Test',
            'Blood Sugar (HbA1c) Report',
            'Chest X-Ray Report',
            'ECG Report',
            'Urine Analysis Report',
            'CT Scan Report',
            'MRI Report',
            'Ultrasound Report',
            'Serum Electrolytes Report',
            'Vitamin D Level Report',
            'Allergy Test Report',
        ];

        $doctors = [
            'Dr. Rahman', 'Dr. Karim', 'Dr. Hasan', 'Dr. Ahmed', 'Dr. Islam',
            'Dr. Begum', 'Dr. Chowdhury', 'Dr. Akter', 'Dr. Khan', 'Dr. Sultana',
            'Dr. Miah', 'Dr. Uddin', 'Dr. Siddiqui', 'Dr. Haque', 'Dr. Ali',
        ];

        $institutions = [
            'Dhaka Medical College Hospital', 'Square Hospital', 'United Hospital',
            'Labaid Hospital', 'Ibn Sina Hospital', 'Popular Diagnostic Centre',
            'Bangabandhu Sheikh Mujib Medical University', 'National Heart Foundation',
            'BIRDEM General Hospital', 'Chittagong Medical College Hospital',
            'Evercare Hospital Dhaka', 'Holy Family Red Crescent Medical College',
        ];

        return [
            'user_id'       => User::inRandomOrder()->first()?->id ?? User::factory(),
            'title'         => $type === 'prescription'
                                ? fake()->randomElement($prescriptionTitles)
                                : fake()->randomElement($reportTitles),
            'type'          => $type,
            'file_path'     => 'uploads/' . fake()->uuid() . '.jpg',
            'summary'       => fake()->paragraph(2),
            'notes'         => fake()->optional(0.5)->sentence(),
            'doctor_name'   => fake()->randomElement($doctors),
            'institution'   => fake()->randomElement($institutions),
            'document_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}
