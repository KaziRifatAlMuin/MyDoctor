<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Symptom;

class PublicHealthController extends Controller
{
    public function showDisease(Disease $disease)
    {
        $disease->load(['symptoms' => fn($q) => $q->orderBy('name')]);

        return view('health.public.disease-show', [
            'disease' => $disease,
        ]);
    }

    public function showSymptom(Symptom $symptom)
    {
        $symptom->load(['diseases' => fn($q) => $q->orderBy('disease_name')]);

        return view('health.public.symptom-show', [
            'symptom' => $symptom,
        ]);
    }
}
