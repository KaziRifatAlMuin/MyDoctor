<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\HealthMetric;
use App\Models\UserSymptom;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\UserDisease;

class SuggestionsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Latest metrics by type
        $latestMetrics = HealthMetric::where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->get()
            ->groupBy('metric_type')
            ->map(fn($g) => $g->first());

        // Recent symptoms (last 14 days)
        $recentSymptoms = UserSymptom::where('user_id', $user->id)
            ->with('symptom')
            ->where('recorded_at', '>=', now()->subDays(14))
            ->orderByDesc('severity_level')
            ->get();

        // Active conditions
        $activeConditions = UserDisease::where('user_id', $user->id)
            ->whereIn('status', ['active', 'chronic', 'managed'])
            ->with('disease')
            ->get();

        // Adherence (last 30 days)
        $logs = MedicineLog::where('user_id', $user->id)
            ->where('date', '>=', now()->subDays(30))
            ->get();

        $totalScheduled = $logs->sum('total_scheduled');
        $totalTaken     = $logs->sum('total_taken');
        $adherenceRate  = $totalScheduled > 0 ? round(($totalTaken / $totalScheduled) * 100) : null;

        // Active medicines
        $medicines = Medicine::where('user_id', $user->id)->get();

        // Build personalized suggestions
        $suggestions = $this->buildSuggestions(
            $user, $latestMetrics, $recentSymptoms, $activeConditions, $adherenceRate, $medicines
        );

        return view('suggestions', compact(
            'user', 'suggestions', 'latestMetrics', 'recentSymptoms',
            'activeConditions', 'adherenceRate', 'medicines'
        ));
    }

    private function buildSuggestions($user, $latestMetrics, $recentSymptoms, $activeConditions, $adherenceRate, $medicines): array
    {
        $suggestions = [];

        // ── Blood Pressure ──
        if ($bp = $latestMetrics->get('blood_pressure')) {
            $sys = $bp->value['systolic'] ?? null;
            $dia = $bp->value['diastolic'] ?? null;
            if ($sys && $dia) {
                if ($sys >= 140 || $dia >= 90) {
                    $suggestions[] = [
                        'icon' => 'fa-heart',
                        'color' => 'danger',
                        'title' => 'High Blood Pressure Detected',
                        'message' => "Your latest BP is {$sys}/{$dia} mmHg which is above normal. Consider reducing salt intake, exercising regularly, and consulting your doctor.",
                        'category' => 'Metric Alert',
                    ];
                } elseif ($sys < 90 || $dia < 60) {
                    $suggestions[] = [
                        'icon' => 'fa-heart',
                        'color' => 'warning',
                        'title' => 'Low Blood Pressure',
                        'message' => "Your BP is {$sys}/{$dia} mmHg. Stay hydrated, avoid standing up too quickly, and increase salt intake slightly if advised by your doctor.",
                        'category' => 'Metric Alert',
                    ];
                }
            }
        }

        // ── Blood Glucose ──
        if ($bg = $latestMetrics->get('blood_glucose')) {
            $val = $bg->value['value'] ?? null;
            if ($val) {
                if ($val > 180) {
                    $suggestions[] = [
                        'icon' => 'fa-tint',
                        'color' => 'danger',
                        'title' => 'High Blood Sugar',
                        'message' => "Your glucose level is {$val} mg/dL. Avoid sugary foods, exercise after meals, and monitor regularly. Consult your doctor.",
                        'category' => 'Metric Alert',
                    ];
                } elseif ($val < 70) {
                    $suggestions[] = [
                        'icon' => 'fa-tint',
                        'color' => 'warning',
                        'title' => 'Low Blood Sugar',
                        'message' => "Glucose at {$val} mg/dL is low. Eat a small snack with fast-acting carbs and recheck in 15 minutes.",
                        'category' => 'Metric Alert',
                    ];
                }
            }
        }

        // ── Heart Rate ──
        if ($hr = $latestMetrics->get('heart_rate')) {
            $bpm = $hr->value['bpm'] ?? null;
            if ($bpm && ($bpm > 100 || $bpm < 60)) {
                $label = $bpm > 100 ? 'elevated' : 'low';
                $suggestions[] = [
                    'icon' => 'fa-heartbeat',
                    'color' => $bpm > 100 ? 'warning' : 'info',
                    'title' => ucfirst($label) . ' Heart Rate',
                    'message' => "Your heart rate is {$bpm} bpm which is {$label}. Practice relaxation techniques, stay hydrated, and see your doctor if this persists.",
                    'category' => 'Metric Alert',
                ];
            }
        }

        // ── Oxygen Saturation ──
        if ($o2 = $latestMetrics->get('oxygen_saturation')) {
            $spo2 = $o2->value['value'] ?? null;
            if ($spo2 && $spo2 < 95) {
                $suggestions[] = [
                    'icon' => 'fa-lungs',
                    'color' => 'danger',
                    'title' => 'Low Oxygen Saturation',
                    'message' => "SpO2 at {$spo2}% is below normal. Practice deep breathing, avoid smoking, and seek medical attention if below 92%.",
                    'category' => 'Metric Alert',
                ];
            }
        }

        // ── BMI ──
        if ($bmi = $latestMetrics->get('bmi')) {
            $val = $bmi->value['value'] ?? null;
            if ($val) {
                if ($val >= 30) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'warning',
                        'title' => 'BMI Indicates Obesity',
                        'message' => "Your BMI is {$val}. Focus on balanced nutrition, regular exercise (150 min/week), and consult a nutritionist.",
                        'category' => 'Lifestyle',
                    ];
                } elseif ($val >= 25) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'info',
                        'title' => 'Overweight BMI',
                        'message' => "BMI of {$val} suggests you're slightly overweight. Small dietary changes and 30 min daily walking can help.",
                        'category' => 'Lifestyle',
                    ];
                } elseif ($val < 18.5) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'warning',
                        'title' => 'Underweight BMI',
                        'message' => "BMI of {$val} is below normal. Increase calorie intake with nutrient-rich foods and consult your doctor.",
                        'category' => 'Lifestyle',
                    ];
                }
            }
        }

        // ── Temperature ──
        if ($temp = $latestMetrics->get('temperature')) {
            $val = $temp->value['value'] ?? null;
            if ($val && $val >= 38) {
                $suggestions[] = [
                    'icon' => 'fa-thermometer-full',
                    'color' => 'danger',
                    'title' => 'Fever Detected',
                    'message' => "Temperature of {$val}°C indicates fever. Rest, stay hydrated, and take paracetamol if needed. See a doctor if it persists.",
                    'category' => 'Metric Alert',
                ];
            }
        }

        // ── Hemoglobin ──
        if ($hb = $latestMetrics->get('hemoglobin')) {
            $val = $hb->value['value'] ?? null;
            if ($val && $val < 12) {
                $suggestions[] = [
                    'icon' => 'fa-tint',
                    'color' => 'warning',
                    'title' => 'Low Hemoglobin',
                    'message' => "Hemoglobin at {$val} g/dL may indicate anemia. Eat iron-rich foods like spinach, eggs, and red meat. Consider iron supplements.",
                    'category' => 'Metric Alert',
                ];
            }
        }

        // ── Adherence ──
        if ($adherenceRate !== null) {
            if ($adherenceRate < 50) {
                $suggestions[] = [
                    'icon' => 'fa-pills',
                    'color' => 'danger',
                    'title' => 'Very Low Medicine Adherence',
                    'message' => "You've only taken {$adherenceRate}% of your medicines in the last 30 days. Set up reminders and keep medicines visible to stay on track.",
                    'category' => 'Adherence',
                ];
            } elseif ($adherenceRate < 80) {
                $suggestions[] = [
                    'icon' => 'fa-pills',
                    'color' => 'warning',
                    'title' => 'Improve Your Medicine Adherence',
                    'message' => "Your adherence rate is {$adherenceRate}%. Try linking medicine times to daily habits (meals, bedtime) to improve consistency.",
                    'category' => 'Adherence',
                ];
            } elseif ($adherenceRate >= 90) {
                $suggestions[] = [
                    'icon' => 'fa-check-circle',
                    'color' => 'success',
                    'title' => 'Excellent Adherence!',
                    'message' => "Great job! {$adherenceRate}% adherence. Keep up the good work — consistency is key to effective treatment.",
                    'category' => 'Adherence',
                ];
            }
        }

        // ── Symptom-based ──
        $severeSymptoms = $recentSymptoms->where('severity_level', '>=', 7);
        if ($severeSymptoms->isNotEmpty()) {
            $names = $severeSymptoms->map(fn($row) => $row->symptom_name)->filter()->unique()->take(3)->implode(', ');
            $suggestions[] = [
                'icon' => 'fa-exclamation-triangle',
                'color' => 'danger',
                'title' => 'Severe Symptoms Reported',
                'message' => "You've reported severe symptoms recently: {$names}. If these persist or worsen, please consult a doctor immediately.",
                'category' => 'Symptom',
            ];
        }

        if ($recentSymptoms->count() >= 5) {
            $suggestions[] = [
                'icon' => 'fa-notes-medical',
                'color' => 'info',
                'title' => 'Multiple Symptoms Logged',
                'message' => "You've logged {$recentSymptoms->count()} symptoms in 14 days. Consider scheduling a check-up to discuss your overall health.",
                'category' => 'Symptom',
            ];
        }

        // ── Condition-based ──
        foreach ($activeConditions as $cond) {
            $name = $cond->disease->disease_name ?? 'Unknown';
            $lowerName = strtolower($name);

            if (str_contains($lowerName, 'diabetes')) {
                $suggestions[] = [
                    'icon' => 'fa-syringe',
                    'color' => 'info',
                    'title' => 'Diabetes Management Tips',
                    'message' => "As a diabetes patient, monitor blood glucose regularly, maintain a low-sugar diet, exercise 30 min/day, and take medicines on time.",
                    'category' => 'Condition',
                ];
            }
            if (str_contains($lowerName, 'hypertension') || str_contains($lowerName, 'blood pressure')) {
                $suggestions[] = [
                    'icon' => 'fa-heart',
                    'color' => 'info',
                    'title' => 'Hypertension Management',
                    'message' => "Managing hypertension: limit sodium to 1500mg/day, exercise regularly, manage stress, and take BP medicines consistently.",
                    'category' => 'Condition',
                ];
            }
            if (str_contains($lowerName, 'asthma')) {
                $suggestions[] = [
                    'icon' => 'fa-lungs',
                    'color' => 'info',
                    'title' => 'Asthma Care Tips',
                    'message' => "Keep your inhaler accessible, avoid triggers (dust, smoke, pollen), and follow your action plan during flare-ups.",
                    'category' => 'Condition',
                ];
            }
        }

        // ── No medicines tracked ──
        if ($medicines->isEmpty()) {
            $suggestions[] = [
                'icon' => 'fa-plus-circle',
                'color' => 'info',
                'title' => 'Start Tracking Medicines',
                'message' => "You haven't added any medicines yet. Add your prescriptions to get reminders and track adherence.",
                'category' => 'Getting Started',
            ];
        }

        // ── No metrics tracked ──
        if ($latestMetrics->isEmpty()) {
            $suggestions[] = [
                'icon' => 'fa-chart-line',
                'color' => 'info',
                'title' => 'Record Health Metrics',
                'message' => "Start recording your blood pressure, glucose, weight, and other metrics to get personalized health insights.",
                'category' => 'Getting Started',
            ];
        }

        // ── General wellness (always shown) ──
        $suggestions[] = [
            'icon' => 'fa-glass-water',
            'color' => 'primary',
            'title' => 'Stay Hydrated',
            'message' => 'Drink at least 8 glasses (2 litres) of water daily. Proper hydration supports digestion, skin health, and energy levels.',
            'category' => 'Wellness',
        ];

        $suggestions[] = [
            'icon' => 'fa-bed',
            'color' => 'primary',
            'title' => 'Prioritize Sleep',
            'message' => 'Aim for 7-9 hours of quality sleep. Maintain a consistent schedule and avoid screens 30 min before bed.',
            'category' => 'Wellness',
        ];

        return $suggestions;
    }
}
