<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\HealthMetric;
use App\Models\UserHealth;
use App\Models\UserSymptom;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\UserDisease;
use App\Services\LiveEnvironmentService;

class SuggestionsController extends Controller
{
    public function index(LiveEnvironmentService $liveEnvironmentService)
    {
        $user = Auth::user();
        $this->ensureMetricDefinitions();
        $liveEnvironment = $liveEnvironmentService->forUser($user);
        $weatherAdvice = data_get($liveEnvironment, 'insights.advisory');
        $weatherAdviceLocation = data_get($liveEnvironment, 'location_label');

        // Latest metrics by type
        $latestMetrics = UserHealth::with('healthMetric')
            ->where('user_id', $user->id)
            ->orderByDesc('recorded_at')
            ->get()
            ->filter(fn(UserHealth $record) => $record->healthMetric !== null)
            ->groupBy(fn(UserHealth $record) => $record->metric_type ?? 'unknown')
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
        $locale = session('locale', config('app.locale') ?? app()->getLocale());

        $suggestions = $this->buildSuggestions(
            $user, $latestMetrics, $recentSymptoms, $activeConditions, $adherenceRate, $medicines, $locale
        );

        return view('suggestions', compact(
            'user', 'suggestions', 'latestMetrics', 'recentSymptoms',
            'activeConditions', 'adherenceRate', 'medicines', 'weatherAdvice', 'weatherAdviceLocation'
        ));
    }

    private function buildSuggestions($user, $latestMetrics, $recentSymptoms, $activeConditions, $adherenceRate, $medicines, ?string $locale = null): array
    {
        $suggestions = [];

        $locale = $locale ?? config('app.locale') ?? app()->getLocale();

        $t = function (string $en, string $bn) use ($locale) {
            return $locale === 'bn' ? $bn : $en;
        };

        // ── Blood Pressure ──
        if ($bp = $latestMetrics->get('blood_pressure')) {
            $sys = $bp->value['systolic'] ?? null;
            $dia = $bp->value['diastolic'] ?? null;
            if ($sys && $dia) {
                if ($sys >= 140 || $dia >= 90) {
                    $suggestions[] = [
                        'icon' => 'fa-heart',
                        'color' => 'danger',
                        'title' => $t('High Blood Pressure Detected', 'উচ্চ রক্তচাপ শনাক্ত করা হয়েছে'),
                        'message' => $t("Your latest BP is {$sys}/{$dia} mmHg which is above normal. Consider reducing salt intake, exercising regularly, and consulting your doctor.", "আপনার সর্বশেষ রক্তচাপ {$sys}/{$dia} mmHg, যা স্বাভাবিকের চেয়ে বেশি। লবণ কমান, নিয়মিত ব্যায়াম করুন এবং ডাক্তারের পরামর্শ নিন।"),
                        'category' => 'Metric Alert',
                    ];
                } elseif ($sys < 90 || $dia < 60) {
                    $suggestions[] = [
                        'icon' => 'fa-heart',
                        'color' => 'warning',
                        'title' => $t('Low Blood Pressure', 'নিম্ন রক্তচাপ'),
                        'message' => $t("Your BP is {$sys}/{$dia} mmHg. Stay hydrated, avoid standing up too quickly, and increase salt intake slightly if advised by your doctor.", "আপনার রক্তচাপ {$sys}/{$dia} mmHg। হাইড্রেটেড থাকুন, হঠাৎ করে উঠা এড়িয়ে চলুন এবং ডাক্তারের পরামর্শে লবণ সামান্য বাড়ান।"),
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
                        'title' => $t('High Blood Sugar', 'উচ্চ রক্তে শর্করা'),
                        'message' => $t("Your glucose level is {$val} mg/dL. Avoid sugary foods, exercise after meals, and monitor regularly. Consult your doctor.", "আপনার গ্লুকোজ স্তর {$val} mg/dL। মিষ্টি খাবার এড়িয়ে চলুন, খাবারের পরে ব্যায়াম করুন এবং নিয়মিত পরীক্ষা করুন। ডাক্তারের পরামর্শ নিন।"),
                        'category' => 'Metric Alert',
                    ];
                } elseif ($val < 70) {
                    $suggestions[] = [
                        'icon' => 'fa-tint',
                        'color' => 'warning',
                        'title' => $t('Low Blood Sugar', 'নিম্ন রক্তে শর্করা'),
                        'message' => $t("Glucose at {$val} mg/dL is low. Eat a small snack with fast-acting carbs and recheck in 15 minutes.", "{$val} mg/dL গ্লুকোজ কম হয়েছে। দ্রুত কার্যকর কার্বোহাইড্রেটযুক্ত ছোট নাস্তা খান এবং 15 মিনিট পর পুনরায় পরীক্ষা করুন।"),
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
                    'title' => $t(ucfirst($label) . ' Heart Rate', ($label === 'elevated' ? 'উচ্চ হার' : 'নিম্ন হার') . ' হার রেট'),
                    'message' => $t("Your heart rate is {$bpm} bpm which is {$label}. Practice relaxation techniques, stay hydrated, and see your doctor if this persists.", "আপনার হার {$bpm} bpm, যা {$label}। ধ্যান/শ্বাস-প্রশ্বাস অনুশীলন করুন, হাইড্রেটেড থাকুন, এবং যদি অবস্থা স্থায়ী হয় তবে ডাক্তারের পরামর্শ নিন।"),
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
                    'title' => $t('Low Oxygen Saturation', 'অক্সিজেন স্যাচুরেশন কম'),
                    'message' => $t("SpO2 at {$spo2}% is below normal. Practice deep breathing, avoid smoking, and seek medical attention if below 92%.", "SpO2 {$spo2}%, যা স্বাভাবিকের নিচে। গভীর শ্বাস-প্রশ্বাসের অনুশীলন করুন, ধূমপান এড়ান, এবং 92%-র নিচে হলে চিকিৎসা নিন।"),
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
                        'title' => $t('BMI Indicates Obesity', 'বিএমআইতে স্থূলতা নির্দেশিত হয়েছে'),
                        'message' => $t("Your BMI is {$val}. Focus on balanced nutrition, regular exercise (150 min/week), and consult a nutritionist.", "আপনার BMI {$val}। সুষম পুষ্টি, নিয়মিত ব্যায়াম (প্রতি সপ্তাহে ১৫০ মিনিট) এবং পুষ্টিবিদের পরামর্শ নিন।"),
                        'category' => 'Lifestyle',
                    ];
                } elseif ($val >= 25) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'info',
                        'title' => $t('Overweight BMI', 'ওভারওয়েট BMI'),
                        'message' => $t("BMI of {$val} suggests you're slightly overweight. Small dietary changes and 30 min daily walking can help.", "BMI {$val} ইঙ্গিত করে আপনি সামান্যওজন বেশি। ছোট ডায়েট পরিবর্তন ও প্রতিদিন ৩০ মিনিট হাঁটা সাহায্য করবে।"),
                        'category' => 'Lifestyle',
                    ];
                } elseif ($val < 18.5) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'warning',
                        'title' => $t('Underweight BMI', 'ওজন কম'),
                        'message' => $t("BMI of {$val} is below normal. Increase calorie intake with nutrient-rich foods and consult your doctor.", "BMI {$val} স্বাভাবিকের নিচে। পুষ্টিগুণসম্পন্ন খাবার দিয়ে ক্যালোরি বাড়ান এবং ডাক্তারের পরামর্শ নিন।"),
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
                    'title' => $t('Fever Detected', 'জ্বর ধরা পড়েছে'),
                    'message' => $t("Temperature of {$val}°C indicates fever. Rest, stay hydrated, and take paracetamol if needed. See a doctor if it persists.", "তাপমাত্রা {$val}°C, যা জ্বর নির্দেশ করে। বিশ্রাম করুন, পর্যাপ্ত পানি পান করুন, প্রয়োজনে প্যারাসিটামল নিন এবং যদি স্থায়ী হয় তবে ডাক্তারের সাথে দেখান।"),
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
                    'title' => $t('Low Hemoglobin', 'কম হিমোগ্লোবিন'),
                    'message' => $t("Hemoglobin at {$val} g/dL may indicate anemia. Eat iron-rich foods like spinach, eggs, and red meat. Consider iron supplements.", "হিমোগ্লোবিন {$val} g/dL আনিমিয়া নির্দেশ করতে পারে। পালং, ডিম, লাল মাংসের মতো লৌহসমৃদ্ধ খাদ্য খান এবং কখনও কখনও লৌহ সাপ্লিমেন্ট বিবেচনা করুন।"),
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
                    'title' => $t('Very Low Medicine Adherence', 'ঔষধ অনুসরণ খুবই কম'),
                    'message' => $t("You've only taken {$adherenceRate}% of your medicines in the last 30 days. Set up reminders and keep medicines visible to stay on track.", "গত ৩০ দিনে আপনি কেবল {$adherenceRate}% ঔষধ নিয়েছেন। রিমাইন্ডার সেট করুন এবং ঔষধ চোখের সামনে রাখুন।"),
                    'category' => 'Adherence',
                ];
            } elseif ($adherenceRate < 80) {
                $suggestions[] = [
                    'icon' => 'fa-pills',
                    'color' => 'warning',
                    'title' => $t('Improve Your Medicine Adherence', 'ঔষধ অনুসরণ উন্নত করুন'),
                    'message' => $t("Your adherence rate is {$adherenceRate}%. Try linking medicine times to daily habits (meals, bedtime) to improve consistency.", "আপনার অনুসরণ হার {$adherenceRate}%। ঔষধ খাওয়ার সময় রোজকার অভ্যাসের সাথে (খানা, ঘুমানোর আগে) যুক্ত করুন যাতে নিয়মিত হওয়া যায়।"),
                    'category' => 'Adherence',
                ];
            } elseif ($adherenceRate >= 90) {
                $suggestions[] = [
                    'icon' => 'fa-check-circle',
                    'color' => 'success',
                    'title' => $t('Excellent Adherence!', 'চমৎকার অনুসরণ!'),
                    'message' => $t("Great job! {$adherenceRate}% adherence. Keep up the good work — consistency is key to effective treatment.", "ভাল কাজ! {$adherenceRate}% অনুসরণ। চালিয়ে যান — ধারাবাহিকতা সফল চিকিৎসার চাবিকাঠি।"),
                    'category' => 'Adherence',
                ];
            }
        }

        // ── Symptom-based ──
        $severeSymptoms = $recentSymptoms->where('severity_level', '>=', 7);
        if ($severeSymptoms->isNotEmpty()) {
            $names = $severeSymptoms->map(function ($row) {
                return $row->symptom_display_name ?? $row->symptom_name ?? null;
            })->filter()->unique()->take(3)->implode(', ');
            $suggestions[] = [
                'icon' => 'fa-exclamation-triangle',
                'color' => 'danger',
                'title' => $t('Severe Symptoms Reported', 'তীব্র উপসর্গ রিপোর্ট করা হয়েছে'),
                'message' => $t("You've reported severe symptoms recently: {$names}. If these persist or worsen, please consult a doctor immediately.", "আপনি সম্প্রতি তীব্র উপসর্গ রিপোর্ট করেছেন: {$names}। যদি এগুলো স্থায়ী হয় বা খারাপ হয়, তবে দ্রুত ডাক্তার দেখান।"),
                'category' => 'Symptom',
            ];
        }

        if ($recentSymptoms->count() >= 5) {
            $suggestions[] = [
                'icon' => 'fa-notes-medical',
                'color' => 'info',
                'title' => $t('Multiple Symptoms Logged', 'একাধিক উপসর্গ লোগ করা হয়েছে'),
                'message' => $t("You've logged {$recentSymptoms->count()} symptoms in 14 days. Consider scheduling a check-up to discuss your overall health.", "গত ১৪ দিনে আপনি {$recentSymptoms->count()} টি উপসর্গ লোগ করেছেন। সামগ্রিক স্বাস্থ্যের জন্য চেক-আপ শিডিউল করতে পারেন।"),
                'category' => 'Symptom',
            ];
        }

        // ── Condition-based ──
        foreach ($activeConditions as $cond) {
            $name = $cond->disease->display_name
                ?? $cond->disease->disease_name
                ?? 'Unknown';
            $lowerName = strtolower($name);

            if (str_contains($lowerName, 'diabetes')) {
                $suggestions[] = [
                    'icon' => 'fa-syringe',
                    'color' => 'info',
                    'title' => $t('Diabetes Management Tips', 'ডায়াবেটিস ব্যবস্থাপনা টিপস'),
                    'message' => $t("As a diabetes patient, monitor blood glucose regularly, maintain a low-sugar diet, exercise 30 min/day, and take medicines on time.", "ডায়াবেটিস রোগী হিসেবে নিয়মিত রক্তের শর্করা পরীক্ষা করুন, কম-চিনি ডায়েট বজায় রাখুন, প্রতিদিন ৩০ মিনিট ব্যায়াম করুন এবং সময়মত ওষুধ নিন।"),
                    'category' => 'Condition',
                ];
            }
            if (str_contains($lowerName, 'hypertension') || str_contains($lowerName, 'blood pressure')) {
                $suggestions[] = [
                    'icon' => 'fa-heart',
                    'color' => 'info',
                    'title' => $t('Hypertension Management', 'উচ্চ রক্তচাপ ব্যবস্থাপনা'),
                    'message' => $t("Managing hypertension: limit sodium to 1500mg/day, exercise regularly, manage stress, and take BP medicines consistently.", "হাইপারটেনশন পরিচালনার জন্য: দৈনিক সোডিয়াম ১৫০০ mg পর্যন্ত সীমাবদ্ধ করুন, নিয়মিত ব্যায়াম করুন, মানসিক চাপ কমান এবং BP ওষুধ সময়মত নিন।"),
                    'category' => 'Condition',
                ];
            }
            if (str_contains($lowerName, 'asthma')) {
                $suggestions[] = [
                    'icon' => 'fa-lungs',
                    'color' => 'info',
                    'title' => $t('Asthma Care Tips', 'অ্যাজমা যত্ন টিপস'),
                    'message' => $t("Keep your inhaler accessible, avoid triggers (dust, smoke, pollen), and follow your action plan during flare-ups.", "ইনহেলার সহজে পৌঁছায় রাখুন, ট্রিগার (ধুলো, ধোঁয়া, পরাগ) এড়িয়ে চলুন এবং ফ্লেয়ার-আপ হলে আপনার অ্যাকশন প্ল্যান অনুসরণ করুন।"),
                    'category' => 'Condition',
                ];
            }
        }

        // ── No medicines tracked ──
        if ($medicines->isEmpty()) {
            $suggestions[] = [
                'icon' => 'fa-plus-circle',
                'color' => 'info',
                'title' => $t('Start Tracking Medicines', 'ঔষধ ট্র্যাক শুরু করুন'),
                'message' => $t("You haven't added any medicines yet. Add your prescriptions to get reminders and track adherence.", "আপনি এখনও কোন ঔষধ যোগ করেননি। রিমাইন্ডার ও অনুসরণ ট্র্যাকিং পাওয়ার জন্য প্রেসক্রিপশন যোগ করুন।"),
                'category' => 'Getting Started',
            ];
        }

        // ── No metrics tracked ──
        if ($latestMetrics->isEmpty()) {
            $suggestions[] = [
                'icon' => 'fa-chart-line',
                'color' => 'info',
                'title' => $t('Record Health Metrics', 'স্বাস্থ্য মেট্রিক রেকর্ড করুন'),
                'message' => $t("Start recording your blood pressure, glucose, weight, and other metrics to get personalized health insights.", "ব্যক্তিগত স্বাস্থ্য বিশ্লেষণের জন্য আপনার রক্তচাপ, গ্লুকোজ, ওজন ও অন্যান্য মেট্রিক রেকর্ড করা শুরু করুন।"),
                'category' => 'Getting Started',
            ];
        }

        // ── General wellness (always shown) ──
        $suggestions[] = [
            'icon' => 'fa-glass-water',
            'color' => 'primary',
            'title' => $t('Stay Hydrated', 'পর্যাপ্ত পানি পান করুন'),
            'message' => $t('Drink at least 8 glasses (2 litres) of water daily. Proper hydration supports digestion, skin health, and energy levels.', 'প্রতিদিন অন্তত ৮ গ্লাস (২ লি) পানি পান করুন। পর্যাপ্ত জল শোষণ, ত্বক ও শক্তি স্তরকে সমর্থন করে।'),
            'category' => 'Wellness',
        ];

        $suggestions[] = [
            'icon' => 'fa-bed',
            'color' => 'primary',
            'title' => $t('Prioritize Sleep', 'ঘুমকে গুরুত্ব দিন'),
            'message' => $t('Aim for 7-9 hours of quality sleep. Maintain a consistent schedule and avoid screens 30 min before bed.', 'প্রতিদিন ৭-৯ ঘন্টা ভালো ঘুম লক্ষ্য করুন। নিয়মিত ঘুমের সময় বজায় রাখুন এবং বিছানার ৩০ মিনিট আগে স্ক্রিন এড়িয়ে চলুন।'),
            'category' => 'Wellness',
        ];

        // Additional automatic suggestions
        $suggestions[] = [
            'icon' => 'fa-user-clock',
            'color' => 'info',
            'title' => $t('Take Short Breaks', 'সংক্ষিপ্ত বিরতি নিন'),
            'message' => $t('If you work long hours, take a 5-10 minute break every hour to stretch and rest your eyes.', 'যদি আপনি দীর্ঘ সময় কাজ করেন, প্রতি ঘন্টায় ৫-১০ মিনিট বিরতি নিন, স্ট্রেচ করুন এবং চোখকে বিশ্রাম দিন।'),
            'category' => 'Lifestyle',
        ];

        $suggestions[] = [
            'icon' => 'fa-apple-alt',
            'color' => 'info',
            'title' => $t('Balanced Diet Tip', 'সুষম ডায়েট টিপ'),
            'message' => $t('Include vegetables, lean protein, whole grains, and fruits in your daily meals for better long-term health.', 'দীর্ঘকালীন সুস্থতার জন্য প্রতিদিনের খাবারে সবজি, লীন প্রোটিন, সস্তা শস্য ও ফল যোগ করুন।'),
            'category' => 'Lifestyle',
        ];

        return $suggestions;
    }

    private function ensureMetricDefinitions(): void
    {
        HealthMetric::seedDefaults();
    }
}