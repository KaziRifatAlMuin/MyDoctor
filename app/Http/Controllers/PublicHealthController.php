<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Symptom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicHealthController extends Controller
{
    public function indexDiseases(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        $diseases = Disease::query()
            ->withCount('symptoms')
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('disease_name', 'like', "%{$query}%");
                    if (Schema::hasColumn('diseases', 'bangla_name')) {
                        $inner->orWhere('bangla_name', 'like', "%{$query}%");
                    }
                    if (Schema::hasColumn('diseases', 'disease_name_bn')) {
                        $inner->orWhere('disease_name_bn', 'like', "%{$query}%");
                    }
                });
            })
            ->orderBy('disease_name')
            ->paginate(30)
            ->withQueryString();

        return view('health.public.diseases-index', [
            'diseases' => $diseases,
            'query' => $query,
        ]);
    }

    public function indexSymptoms(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        $symptoms = Symptom::query()
            ->withCount('diseases')
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', "%{$query}%");
                    if (Schema::hasColumn('symptoms', 'bangla_name')) {
                        $inner->orWhere('bangla_name', 'like', "%{$query}%");
                    }
                    if (Schema::hasColumn('symptoms', 'name_bn')) {
                        $inner->orWhere('name_bn', 'like', "%{$query}%");
                    }
                });
            })
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        return view('health.public.symptoms-index', [
            'symptoms' => $symptoms,
            'query' => $query,
        ]);
    }

    public function showDisease(Disease $disease)
    {
        $disease->load([
            'symptoms' => fn($q) => $q->orderBy('name'),
            'posts.user',
        ]);

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
