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
            'Pneumonia', 'Bronchitis', 'Sinusitis', 'Tonsillitis', 'Appendicitis',
            'Gallstones', 'Pancreatitis', 'Peptic Ulcer Disease', 'Crohn\'s Disease', 'Ulcerative Colitis',
            'Chronic Bronchitis', 'Emphysema', 'Pulmonary Fibrosis', 'Sarcoidosis', 'Meningitis',
            'Encephalitis', 'Bell\'s Palsy', 'Carpal Tunnel Syndrome', 'Sciatica', 'Herniated Disc',
            'Osteoarthritis', 'Bursitis', 'Tendinitis', 'Plantar Fasciitis', 'Scoliosis',
            'Cataracts', 'Glaucoma', 'Macular Degeneration', 'Conjunctivitis', 'Retinopathy',
            'Hearing Loss', 'Tinnitus', 'Vertigo', 'Otitis Media', 'Deviated Septum',
            'Atrial Fibrillation', 'Deep Vein Thrombosis', 'Pulmonary Embolism', 'Peripheral Artery Disease', 'Varicose Veins',
            'Leukemia', 'Lymphoma', 'Breast Cancer', 'Lung Cancer', 'Colorectal Cancer',
            'Prostate Cancer', 'Skin Cancer', 'Thyroid Cancer', 'Bladder Cancer', 'Pancreatic Cancer',
            'Ovarian Cancer', 'Cervical Cancer', 'Liver Cancer', 'Stomach Cancer', 'Esophageal Cancer',
            'Psoriatic Arthritis', 'Ankylosing Spondylitis', 'Sjogren\'s Syndrome', 'Raynaud\'s Disease', 'Vitiligo',
            'Alopecia', 'Rosacea', 'Acne', 'Dermatitis', 'Cellulitis',
            'Hypothyroidism', 'Hyperthyroidism', 'Addison\'s Disease', 'Cushing\'s Syndrome', 'Gestational Diabetes',
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
