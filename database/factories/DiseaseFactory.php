<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DiseaseFactory extends Factory
{
    public function definition(): array
    {
        static $diseases = [
            'Diabetes Type 1', 'Diabetes Type 2', 'Hypertension', 'Asthma', 'Chronic Kidney Disease',
            'Heart Failure', 'Coronary Artery Disease', 'COPD', 'Stroke', 'Arthritis',
            'Osteoporosis', 'Thyroid Disorder', 'Anemia', 'Depression', 'Anxiety Disorder',
            'Migraine', 'Epilepsy', 'Parkinson\'s Disease', 'Alzheimer\'s Disease', 'Gastric Ulcer',
            'GERD', 'Irritable Bowel Syndrome', 'Liver Cirrhosis', 'Hepatitis B', 'Hepatitis C',
            'Tuberculosis', 'Dengue Fever', 'Malaria', 'Typhoid', 'Chickenpox',
            'Psoriasis', 'Eczema', 'Urinary Tract Infection', 'Kidney Stones', 'Gout',
            'Rheumatoid Arthritis', 'Lupus', 'Multiple Sclerosis', 'Celiac Disease', 'Fibromyalgia',
            'Chronic Fatigue Syndrome', 'Sleep Apnea', 'Obesity', 'Vitamin D Deficiency', 'Iron Deficiency',
            'High Cholesterol', 'Fatty Liver', 'Polycystic Ovary Syndrome', 'Endometriosis', 'Prostate Enlargement',
        ];

        static $index = 0;
        $name = $diseases[$index % count($diseases)];
        $index++;

        return [
            'disease_name' => $name,
            'description'  => fake()->paragraph(2),
        ];
    }
}
