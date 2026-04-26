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
    /**
     * Display the suggestions page with personalized health recommendations.
     *
     * @param LiveEnvironmentService $liveEnvironmentService
     * @return \Illuminate\View\View
     */
    public function index(LiveEnvironmentService $liveEnvironmentService)
    {
        $user = Auth::user();
        $this->ensureMetricDefinitions();
        $liveEnvironment = $liveEnvironmentService->forUser($user);
        $weatherAdvice = data_get($liveEnvironment, 'insights.advisory');
        $weatherAdviceLocation = data_get($liveEnvironment, 'location_label');

        // Latest metrics by type (most recent per metric type)
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

        // Active conditions (active, chronic, or managed status)
        $activeConditions = UserDisease::where('user_id', $user->id)
            ->whereIn('status', ['active', 'chronic', 'managed'])
            ->with('disease')
            ->get();

        // Adherence calculation (last 30 days)
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

    /**
     * Build personalized health suggestions based on user data.
     *
     * Clinical thresholds are based on:
     * - ACC/AHA 2017 Hypertension Guidelines (Blood Pressure)
     * - ADA Standards of Medical Care 2024 (Blood Glucose)
     * - AHA Heart Rate Guidelines (Heart Rate)
     * - ATS/ERS Oxygen Guidelines (SpO2)
     * - WHO BMI Classification (BMI)
     * - NICE Fever Guidelines (Temperature)
     * - WHO Anemia Diagnosis (Hemoglobin - gender-based)
     * - WHO Adherence Research (Medicine Adherence)
     * - American Pain Society (Symptom Severity)
     *
     * @param \App\Models\User $user
     * @param \Illuminate\Support\Collection $latestMetrics
     * @param \Illuminate\Support\Collection $recentSymptoms
     * @param \Illuminate\Support\Collection $activeConditions
     * @param int|null $adherenceRate
     * @param \Illuminate\Support\Collection $medicines
     * @param string|null $locale
     * @return array
     */
    private function buildSuggestions($user, $latestMetrics, $recentSymptoms, $activeConditions, $adherenceRate, $medicines, ?string $locale = null): array
    {
        $suggestions = [];

        $locale = $locale ?? config('app.locale') ?? app()->getLocale();

        $t = function (string $en, string $bn) use ($locale) {
            return $locale === 'bn' ? $bn : $en;
        };

        // ═══════════════════════════════════════════════════════════════════
        // BLOOD PRESSURE
        // Reference: ACC/AHA 2017 Hypertension Guidelines
        // Normal: <120/80 mmHg | Elevated: 120-129/<80 | Stage 1: 130-139/80-89
        // Stage 2: ≥140/90 | Hypotension: <90/60
        // ═══════════════════════════════════════════════════════════════════
        if ($bp = $latestMetrics->get('blood_pressure')) {
            $sys = $bp->value['systolic'] ?? null;
            $dia = $bp->value['diastolic'] ?? null;
            if ($sys && $dia) {
                // Stage 2 Hypertension (ACC/AHA 2017)
                if ($sys >= 140 || $dia >= 90) {
                    $suggestions[] = [
                        'icon' => 'fa-heart',
                        'color' => 'danger',
                        'title' => $t('High Blood Pressure (Stage 2)', 'উচ্চ রক্তচাপ (স্টেজ ২)'),
                        'message' => $t("Your latest BP is {$sys}/{$dia} mmHg, which meets Stage 2 hypertension criteria. Please consult your doctor promptly for management. [ACC/AHA 2017]",
                            "আপনার সর্বশেষ রক্তচাপ {$sys}/{$dia} mmHg, যা স্টেজ ২ হাইপারটেনশনের মানদণ্ড পূরণ করে। ব্যবস্থাপনার জন্য দয়া করে আপনার ডাক্তারের সাথে দ্রুত পরামর্শ করুন। [ACC/AHA ২০১৭]"),
                        'category' => 'Metric Alert',
                    ];
                }
                // Stage 1 Hypertension (ACC/AHA 2017)
                elseif ($sys >= 130 || $dia >= 80) {
                    $suggestions[] = [
                        'icon' => 'fa-heart',
                        'color' => 'warning',
                        'title' => $t('High Blood Pressure (Stage 1)', 'উচ্চ রক্তচাপ (স্টেজ ১)'),
                        'message' => $t("Your latest BP is {$sys}/{$dia} mmHg, which is elevated. Consider reducing salt intake, exercising regularly, and monitoring your BP at home. [ACC/AHA 2017]",
                            "আপনার সর্বশেষ রক্তচাপ {$sys}/{$dia} mmHg, যা উচ্চতর। লবণ কমান, নিয়মিত ব্যায়াম করুন এবং বাড়িতে আপনার BP পর্যবেক্ষণ করার কথা বিবেচনা করুন। [ACC/AHA ২০১৭]"),
                        'category' => 'Metric Alert',
                    ];
                }
                // Hypotension (ACC/AHA)
                elseif ($sys < 90 || $dia < 60) {
                    $suggestions[] = [
                        'icon' => 'fa-heart',
                        'color' => 'warning',
                        'title' => $t('Low Blood Pressure (Hypotension)', 'নিম্ন রক্তচাপ (হাইপোটেনশন)'),
                        'message' => $t("Your BP is {$sys}/{$dia} mmHg. Stay hydrated, avoid standing up too quickly, and consult your doctor if you experience dizziness or fainting.",
                            "আপনার রক্তচাপ {$sys}/{$dia} mmHg। হাইড্রেটেড থাকুন, হঠাৎ করে উঠা এড়িয়ে চলুন এবং যদি আপনি মাথা ঘোরা বা অজ্ঞান হয়ে যাওয়ার অভিজ্ঞতা পান তবে আপনার ডাক্তারের সাথে পরামর্শ করুন।"),
                        'category' => 'Metric Alert',
                    ];
                }
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // BLOOD GLUCOSE
        // Reference: ADA Standards of Medical Care 2024
        // Normal fasting: <100 mg/dL | Prediabetes: 100-125 mg/dL
        // Diabetes: ≥126 mg/dL (fasting) or ≥200 mg/dL (random)
        // Hypoglycemia: <70 mg/dL
        // ═══════════════════════════════════════════════════════════════════
        if ($bg = $latestMetrics->get('blood_glucose')) {
            $val = $bg->value['value'] ?? null;
            $context = $bg->value['context'] ?? 'random'; // fasting, postprandial, random
            
            if ($val) {
                // Diabetic range (ADA 2024)
                $isDiabeticRange = ($context === 'fasting' && $val >= 126) || 
                                   (($context === 'random' || $context === 'postprandial') && $val >= 200);
                
                if ($isDiabeticRange || $val > 180) {
                    $suggestions[] = [
                        'icon' => 'fa-tint',
                        'color' => 'danger',
                        'title' => $t('High Blood Sugar (Hyperglycemia)', 'উচ্চ রক্তে শর্করা (হাইপারগ্লাইসেমিয়া)'),
                        'message' => $t("Your glucose level is {$val} mg/dL ({$context}). This is above the recommended range. Consult your doctor for proper management. [ADA 2024]",
                            "আপনার গ্লুকোজ স্তর {$val} mg/dL ({$context})। এটি প্রস্তাবিত সীমার উপরে। সঠিক ব্যবস্থাপনার জন্য আপনার ডাক্তারের সাথে পরামর্শ করুন। [ADA ২০২৪]"),
                        'category' => 'Metric Alert',
                    ];
                } 
                // Prediabetes range (ADA 2024)
                elseif ($context === 'fasting' && $val >= 100 && $val < 126) {
                    $suggestions[] = [
                        'icon' => 'fa-tint',
                        'color' => 'warning',
                        'title' => $t('Prediabetes Range', 'প্রিডায়াবেটিস রেঞ্জ'),
                        'message' => $t("Your fasting glucose is {$val} mg/dL, which is in the prediabetes range. Lifestyle modifications including diet and exercise can help prevent progression to diabetes. [ADA 2024]",
                            "আপনার উপবাসের গ্লুকোজ {$val} mg/dL, যা প্রিডায়াবেটিস রেঞ্জে রয়েছে। ডায়েট এবং ব্যায়াম সহ জীবনযাত্রার পরিবর্তনগুলি ডায়াবেটিসের অগ্রগতি রোধ করতে সাহায্য করতে পারে। [ADA ২০২৪]"),
                        'category' => 'Metric Alert',
                    ];
                }
                // Hypoglycemia (ADA 2024)
                elseif ($val < 70) {
                    $suggestions[] = [
                        'icon' => 'fa-tint',
                        'color' => 'warning',
                        'title' => $t('Low Blood Sugar (Hypoglycemia)', 'নিম্ন রক্তে শর্করা (হাইপোগ্লাইসেমিয়া)'),
                        'message' => $t("Glucose at {$val} mg/dL is low. Consume 15-20g of fast-acting carbohydrates (glucose tablets, juice, or regular soda) and recheck in 15 minutes. [ADA 2024]",
                            "{$val} mg/dL গ্লুকোজ কম হয়েছে। দ্রুত কার্যকর কার্বোহাইড্রেট (গ্লুকোজ ট্যাবলেট, জুস, বা নিয়মিত সোডা) খান এবং ১৫ মিনিট পর পুনরায় পরীক্ষা করুন। [ADA ২০২৪]"),
                        'category' => 'Metric Alert',
                    ];
                }
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // HEART RATE
        // Reference: American Heart Association
        // Normal resting: 60-100 bpm | Tachycardia: >100 bpm
        // Bradycardia: <60 bpm (often normal in athletes)
        // ═══════════════════════════════════════════════════════════════════
        if ($hr = $latestMetrics->get('heart_rate')) {
            $bpm = $hr->value['bpm'] ?? null;
            if ($bpm && $bpm > 100) {
                $suggestions[] = [
                    'icon' => 'fa-heartbeat',
                    'color' => 'warning',
                    'title' => $t('Elevated Heart Rate (Tachycardia)', 'উচ্চ হৃদস্পন্দন (ট্যাকিকার্ডিয়া)'),
                    'message' => $t("Your resting heart rate is {$bpm} bpm, which is above the normal range (60-100 bpm). Practice relaxation techniques, stay hydrated, and consult your doctor if this persists. [AHA]",
                        "আপনার বিশ্রামরত হৃদস্পন্দন {$bpm} bpm, যা স্বাভাবিক সীমার (৬০-১০০ bpm) উপরে। শিথিলকরণ কৌশল অনুশীলন করুন, হাইড্রেটেড থাকুন এবং যদি এটি স্থায়ী হয় তবে আপনার ডাক্তারের সাথে পরামর্শ করুন। [AHA]"),
                    'category' => 'Metric Alert',
                ];
            } 
            elseif ($bpm && $bpm < 60) {
                $suggestions[] = [
                    'icon' => 'fa-heartbeat',
                    'color' => 'info',
                    'title' => $t('Low Heart Rate (Bradycardia)', 'নিম্ন হৃদস্পন্দন (ব্র্যাডিকার্ডিয়া)'),
                    'message' => $t("Your resting heart rate is {$bpm} bpm. While this can be normal for athletes, consult your doctor if you experience dizziness, fatigue, or fainting. [AHA]",
                        "আপনার বিশ্রামরত হৃদস্পন্দন {$bpm} bpm। এটি ক্রীড়াবিদদের জন্য স্বাভাবিক হতে পারে, তবে যদি আপনি মাথা ঘোরা, ক্লান্তি বা অজ্ঞান হয়ে যাওয়ার অভিজ্ঞতা পান তবে আপনার ডাক্তারের সাথে পরামর্শ করুন। [AHA]"),
                    'category' => 'Metric Alert',
                ];
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // OXYGEN SATURATION (SpO2)
        // Reference: ATS/ERS Guidelines
        // Normal: 95-100% | Mild hypoxemia: 91-94%
        // Moderate: 86-90% | Severe: ≤85% | Critical: <90% requires attention
        // ═══════════════════════════════════════════════════════════════════
        if ($o2 = $latestMetrics->get('oxygen_saturation')) {
            $spo2 = $o2->value['value'] ?? null;
            if ($spo2) {
                if ($spo2 < 90) {
                    $suggestions[] = [
                        'icon' => 'fa-lungs',
                        'color' => 'danger',
                        'title' => $t('Critical Oxygen Level', 'গুরুতর অক্সিজেন মাত্রা'),
                        'message' => $t("Your SpO2 is {$spo2}%. This is critically low. Please seek immediate medical attention. [ATS/ERS]",
                            "আপনার SpO2 {$spo2}%। এটি গুরুতরভাবে কম। অবিলম্বে চিকিৎসা সহায়তা নিন। [ATS/ERS]"),
                        'category' => 'Metric Alert',
                    ];
                } 
                elseif ($spo2 < 92) {
                    $suggestions[] = [
                        'icon' => 'fa-lungs',
                        'color' => 'warning',
                        'title' => $t('Low Oxygen Saturation', 'নিম্ন অক্সিজেন স্যাচুরেশন'),
                        'message' => $t("Your SpO2 is {$spo2}%. This is below normal. Practice deep breathing exercises and consult your healthcare provider. [ATS/ERS]",
                            "আপনার SpO2 {$spo2}%। এটি স্বাভাবিকের নিচে। গভীর শ্বাস-প্রশ্বাসের অনুশীলন করুন এবং আপনার স্বাস্থ্যসেবা প্রদানকারীর সাথে পরামর্শ করুন। [ATS/ERS]"),
                        'category' => 'Metric Alert',
                    ];
                }
                elseif ($spo2 < 95) {
                    $suggestions[] = [
                        'icon' => 'fa-lungs',
                        'color' => 'info',
                        'title' => $t('Monitor Oxygen Level', 'অক্সিজেন মাত্রা পর্যবেক্ষণ করুন'),
                        'message' => $t("Your SpO2 is {$spo2}%. While not critical, this is slightly below optimal. Monitor and consult your doctor if it drops further. [ATS/ERS]",
                            "আপনার SpO2 {$spo2}%। যদিও এটি গুরুতর নয়, এটি সর্বোত্তমের চেয়ে সামান্য কম। পর্যবেক্ষণ করুন এবং যদি এটি আরও কমে যায় তবে আপনার ডাক্তারের সাথে পরামর্শ করুন। [ATS/ERS]"),
                        'category' => 'Metric Alert',
                    ];
                }
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // BMI (Body Mass Index)
        // Reference: World Health Organization Classification
        // Underweight: <18.5 | Normal: 18.5-24.9 | Overweight: 25-29.9
        // Obesity Class I: 30-34.9 | Class II: 35-39.9 | Class III: ≥40
        // ═══════════════════════════════════════════════════════════════════
        if ($bmi = $latestMetrics->get('bmi')) {
            $val = $bmi->value['value'] ?? null;
            if ($val) {
                if ($val >= 40) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'danger',
                        'title' => $t('Severe Obesity (Class III)', 'গুরুতর স্থূলতা (ক্লাস III)'),
                        'message' => $t("Your BMI is {$val} (Class III Obesity). Please consult your doctor for a comprehensive weight management plan. [WHO]",
                            "আপনার BMI {$val} (ক্লাস III স্থূলতা)। একটি বিস্তৃত ওজন ব্যবস্থাপনা পরিকল্পনার জন্য আপনার ডাক্তারের সাথে পরামর্শ করুন। [WHO]"),
                        'category' => 'Lifestyle',
                    ];
                } 
                elseif ($val >= 35) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'warning',
                        'title' => $t('Severe Obesity (Class II)', 'গুরুতর স্থূলতা (ক্লাস II)'),
                        'message' => $t("Your BMI is {$val} (Class II Obesity). Consider consulting a healthcare provider for weight management support. [WHO]",
                            "আপনার BMI {$val} (ক্লাস II স্থূলতা)। ওজন ব্যবস্থাপনা সমর্থনের জন্য একজন স্বাস্থ্যসেবা প্রদানকারীর সাথে পরামর্শ করার কথা বিবেচনা করুন। [WHO]"),
                        'category' => 'Lifestyle',
                    ];
                }
                elseif ($val >= 30) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'warning',
                        'title' => $t('Obesity (Class I)', 'স্থূলতা (ক্লাস I)'),
                        'message' => $t("Your BMI is {$val} (Obese). Focus on balanced nutrition, regular exercise (150 min/week), and consult a nutritionist. [WHO]",
                            "আপনার BMI {$val} (স্থূল)। সুষম পুষ্টি, নিয়মিত ব্যায়াম (প্রতি সপ্তাহে ১৫০ মিনিট) এবং পুষ্টিবিদের পরামর্শ নিন। [WHO]"),
                        'category' => 'Lifestyle',
                    ];
                } 
                elseif ($val >= 25) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'info',
                        'title' => $t('Overweight', 'অধিক ওজন'),
                        'message' => $t("BMI of {$val} suggests you're overweight. Small dietary changes and 30 min daily walking can help. [WHO]",
                            "BMI {$val} ইঙ্গিত করে আপনি ওজন বেশি। ছোট ডায়েট পরিবর্তন ও প্রতিদিন ৩০ মিনিট হাঁটা সাহায্য করবে। [WHO]"),
                        'category' => 'Lifestyle',
                    ];
                } 
                elseif ($val < 18.5) {
                    $suggestions[] = [
                        'icon' => 'fa-weight',
                        'color' => 'warning',
                        'title' => $t('Underweight', 'ওজন কম'),
                        'message' => $t("BMI of {$val} is below normal. Increase calorie intake with nutrient-rich foods and consult your doctor. [WHO]",
                            "BMI {$val} স্বাভাবিকের নিচে। পুষ্টিগুণসম্পন্ন খাবার দিয়ে ক্যালোরি বাড়ান এবং ডাক্তারের পরামর্শ নিন। [WHO]"),
                        'category' => 'Lifestyle',
                    ];
                }
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // TEMPERATURE
        // Reference: NICE Fever Guidelines
        // Normal: 36.1-37.2°C (97-99°F) | Fever: ≥38.0°C (100.4°F)
        // High fever: ≥39.5°C (103.1°F) | Hypothermia: <35.0°C (95.0°F)
        // ═══════════════════════════════════════════════════════════════════
        if ($temp = $latestMetrics->get('temperature')) {
            $val = $temp->value['value'] ?? null;
            if ($val) {
                if ($val >= 39.5) {
                    $suggestions[] = [
                        'icon' => 'fa-thermometer-full',
                        'color' => 'danger',
                        'title' => $t('High Fever Detected', 'উচ্চ জ্বর ধরা পড়েছে'),
                        'message' => $t("Temperature of {$val}°C indicates high fever. Seek medical attention promptly. [NICE]",
                            "তাপমাত্রা {$val}°C, যা উচ্চ জ্বর নির্দেশ করে। দ্রুত চিকিৎসা নিন। [NICE]"),
                        'category' => 'Metric Alert',
                    ];
                } 
                elseif ($val >= 38) {
                    $suggestions[] = [
                        'icon' => 'fa-thermometer-full',
                        'color' => 'warning',
                        'title' => $t('Fever Detected', 'জ্বর ধরা পড়েছে'),
                        'message' => $t("Temperature of {$val}°C indicates fever. Rest, stay hydrated, and monitor your symptoms. Consult a doctor if fever persists beyond 3 days or exceeds 39.5°C. [NICE]",
                            "তাপমাত্রা {$val}°C, যা জ্বর নির্দেশ করে। বিশ্রাম করুন, পর্যাপ্ত পানি পান করুন এবং আপনার উপসর্গগুলি পর্যবেক্ষণ করুন। জ্বর ৩ দিনের বেশি স্থায়ী হলে বা ৩৯.৫°C ছাড়িয়ে গেলে ডাক্তারের সাথে পরামর্শ করুন। [NICE]"),
                        'category' => 'Metric Alert',
                    ];
                }
                elseif ($val < 35) {
                    $suggestions[] = [
                        'icon' => 'fa-thermometer-empty',
                        'color' => 'warning',
                        'title' => $t('Low Body Temperature (Hypothermia)', 'নিম্ন শরীরের তাপমাত্রা (হাইপোথার্মিয়া)'),
                        'message' => $t("Your temperature is {$val}°C, which indicates hypothermia. Seek medical attention immediately. [NICE]",
                            "আপনার তাপমাত্রা {$val}°C, যা হাইপোথার্মিয়া নির্দেশ করে। অবিলম্বে চিকিৎসা নিন। [NICE]"),
                        'category' => 'Metric Alert',
                    ];
                }
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // HEMOGLOBIN (Gender-based thresholds)
        // Reference: World Health Organization Anemia Diagnosis
        // Male: <13.5 g/dL indicates anemia
        // Female: <12.0 g/dL indicates anemia
        // Pregnancy: <11.0 g/dL indicates anemia
        // ═══════════════════════════════════════════════════════════════════
        if ($hb = $latestMetrics->get('hemoglobin')) {
            $val = $hb->value['value'] ?? null;
            $userGender = $user->gender ?? 'other';
            
            // WHO gender-based thresholds
            $isLow = false;
            $threshold = 12.0;
            $genderText = 'female';
            
            if ($userGender === 'male') {
                $isLow = $val < 13.5;
                $threshold = 13.5;
                $genderText = 'male';
            } elseif ($userGender === 'female') {
                $isLow = $val < 12.0;
                $threshold = 12.0;
                $genderText = 'female';
            } else {
                $isLow = $val < 12.0;
                $threshold = 12.0;
                $genderText = 'adult';
            }
            
            if ($isLow && $val) {
                $suggestions[] = [
                    'icon' => 'fa-tint',
                    'color' => 'warning',
                    'title' => $t('Low Hemoglobin (Anemia)', 'কম হিমোগ্লোবিন (অ্যানিমিয়া)'),
                    'message' => $t("Hemoglobin at {$val} g/dL is below the normal threshold of {$threshold} g/dL for {$genderText}s. This may indicate anemia. Eat iron-rich foods (spinach, eggs, lean red meat) and consult your doctor for proper diagnosis. [WHO]",
                        "হিমোগ্লোবিন {$val} g/dL {$genderText}দের জন্য স্বাভাবিক সীমার {$threshold} g/dL এর নিচে। এটি রক্তাল্পতা নির্দেশ করতে পারে। আয়রন সমৃদ্ধ খাবার (পালং শাক, ডিম, চর্বিহীন লাল মাংস) খান এবং সঠিক রোগ নির্ণয়ের জন্য আপনার ডাক্তারের সাথে পরামর্শ করুন। [WHO]"),
                    'category' => 'Metric Alert',
                ];
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // MEDICINE ADHERENCE
        // References:
        // - WHO (2003): <50% adherence significantly increases hospitalization risk
        // - Jimmy & Jose (2011): 50-79% leads to suboptimal outcomes
        // - Osterberg & Blaschke (2005): ≥90% target for chronic disease management
        // ═══════════════════════════════════════════════════════════════════
        if ($adherenceRate !== null) {
            if ($adherenceRate < 50) {
                $suggestions[] = [
                    'icon' => 'fa-pills',
                    'color' => 'danger',
                    'title' => $t('Critical Medicine Adherence', 'ঔষধ অনুসরণ গুরুতরভাবে কম'),
                    'message' => $t("Your adherence rate is {$adherenceRate}%. WHO research shows that <50% adherence significantly increases hospitalization risk [WHO 2003]. Set up reminders, use a pill organizer, and discuss barriers with your doctor.",
                        "আপনার অনুসরণ হার {$adherenceRate}%। WHO গবেষণা দেখায় যে <৫০% অনুসরণ হাসপাতালে ভর্তির ঝুঁকি উল্লেখযোগ্যভাবে বাড়ায় [WHO ২০০৩]। রিমাইন্ডার সেট আপ করুন, একটি পিল অর্গানাইজার ব্যবহার করুন এবং আপনার ডাক্তারের সাথে বাধাগুলি নিয়ে আলোচনা করুন।"),
                    'category' => 'Adherence',
                ];
            } elseif ($adherenceRate < 80) {
                $suggestions[] = [
                    'icon' => 'fa-pills',
                    'color' => 'warning',
                    'title' => $t('Improve Medicine Adherence', 'ঔষধ অনুসরণ উন্নত করুন'),
                    'message' => $t("Your adherence rate is {$adherenceRate}%. Research indicates that 50-79% adherence leads to suboptimal treatment outcomes [Jimmy & Jose, 2011]. Try linking medicine times to daily habits and using reminder apps.",
                        "আপনার অনুসরণ হার {$adherenceRate}%। গবেষণা ইঙ্গিত দেয় যে ৫০-৭৯% অনুসরণ উপ-অনুকূল চিকিত্সার ফলাফলের দিকে পরিচালিত করে [Jimmy & Jose, ২০১১]। ঔষধের সময় দৈনন্দিন অভ্যাসের সাথে যুক্ত করার এবং রিমাইন্ডার অ্যাপ ব্যবহার করার চেষ্টা করুন।"),
                    'category' => 'Adherence',
                ];
            } elseif ($adherenceRate >= 90) {
                $suggestions[] = [
                    'icon' => 'fa-check-circle',
                    'color' => 'success',
                    'title' => $t('Excellent Adherence!', 'চমৎকার অনুসরণ!'),
                    'message' => $t("Excellent! Your adherence rate is {$adherenceRate}%. Osterberg & Blaschke (2005) identified ≥90% adherence as the threshold for effective chronic disease management. Keep up the great work!",
                        "চমৎকার! আপনার অনুসরণ হার {$adherenceRate}%। Osterberg & Blaschke (২০০৫) কার্যকর ক্রনিক রোগ ব্যবস্থাপনার জন্য ≥৯০% অনুসরণকে থ্রেশহোল্ড হিসাবে চিহ্নিত করেছেন। ভাল কাজ চালিয়ে যান!"),
                    'category' => 'Adherence',
                ];
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // SYMPTOM-BASED ALERTS
        // Reference: American Pain Society Severity Scale
        // Mild: 1-3 | Moderate: 4-6 | Severe: 7-10
        // ═══════════════════════════════════════════════════════════════════
        $severeSymptoms = $recentSymptoms->where('severity_level', '>=', 7);
        if ($severeSymptoms->isNotEmpty()) {
            $names = $severeSymptoms->map(function ($row) {
                return $row->symptom_display_name ?? $row->symptom_name ?? null;
            })->filter()->unique()->take(3)->implode(', ');
            
            $suggestions[] = [
                'icon' => 'fa-exclamation-triangle',
                'color' => 'danger',
                'title' => $t('Severe Symptoms Reported', 'তীব্র উপসর্গ রিপোর্ট করা হয়েছে'),
                'message' => $t("You've reported severe symptoms (severity ≥7/10): {$names}. The American Pain Society recommends seeking medical evaluation for pain or symptoms rated 7 or higher, especially if persistent or worsening.",
                    "আপনি তীব্র উপসর্গ রিপোর্ট করেছেন (তীব্রতা ≥৭/১০): {$names}। আমেরিকান পেইন সোসাইটি ৭ বা তার বেশি রেটিং সহ ব্যথা বা উপসর্গের জন্য চিকিৎসা মূল্যায়নের সুপারিশ করে, বিশেষ করে যদি স্থায়ী বা খারাপ হয়।"),
                'category' => 'Symptom',
            ];
        }

        // Multiple symptoms threshold (≥5 in 14 days)
        if ($recentSymptoms->count() >= 5) {
            $suggestions[] = [
                'icon' => 'fa-notes-medical',
                'color' => 'info',
                'title' => $t('Multiple Symptoms Logged', 'একাধিক উপসর্গ লগ করা হয়েছে'),
                'message' => $t("You've logged {$recentSymptoms->count()} distinct symptoms in the past 14 days. Research on multimorbidity suggests that multiple concurrent symptoms may indicate an underlying condition requiring comprehensive evaluation. Consider scheduling a check-up with your healthcare provider.",
                    "গত ১৪ দিনে আপনি {$recentSymptoms->count()} টি পৃথক উপসর্গ লগ করেছেন। মাল্টিমর্বিডিটি গবেষণা পরামর্শ দেয় যে একাধিক সহগামী উপসর্গ একটি অন্তর্নিহিত অবস্থা নির্দেশ করতে পারে যার জন্য বিস্তৃত মূল্যায়ন প্রয়োজন। আপনার স্বাস্থ্যসেবা প্রদানকারীর সাথে একটি চেক-আপের সময়সূচী করার কথা বিবেচনা করুন।"),
                'category' => 'Symptom',
            ];
        }

        // ═══════════════════════════════════════════════════════════════════
        // CONDITION-BASED SUGGESTIONS
        // References:
        // - ADA Standards of Medical Care (Diabetes)
        // - ACC/AHA 2017 (Hypertension)
        // - GINA Guidelines (Asthma)
        // ═══════════════════════════════════════════════════════════════════
        foreach ($activeConditions as $cond) {
            $name = $cond->disease->display_name ?? $cond->disease->disease_name ?? 'Unknown';
            $lowerName = strtolower($name);

            // Diabetes Management (ADA Standards of Care)
            if (str_contains($lowerName, 'diabetes')) {
                $suggestions[] = [
                    'icon' => 'fa-syringe',
                    'color' => 'info',
                    'title' => $t('Diabetes Management Tips', 'ডায়াবেটিস ব্যবস্থাপনা টিপস'),
                    'message' => $t("According to ADA guidelines: Monitor HbA1c every 3-6 months (target <7%), maintain blood pressure <130/80 mmHg, check cholesterol annually, and perform annual foot and eye exams. [ADA Standards of Care 2024]",
                        "ADA নির্দেশিকা অনুযায়ী: প্রতি ৩-৬ মাসে HbA1c পর্যবেক্ষণ করুন (লক্ষ্য <৭%), রক্তচাপ <১৩০/৮০ mmHg বজায় রাখুন, বার্ষিক কোলেস্টেরল পরীক্ষা করুন এবং বার্ষিক পা ও চোখ পরীক্ষা করান। [ADA স্ট্যান্ডার্ডস অফ কেয়ার ২০২৪]"),
                    'category' => 'Condition',
                ];
                continue;
            }
            
            // Hypertension Management (ACC/AHA 2017)
            if (str_contains($lowerName, 'hypertension') || str_contains($lowerName, 'blood pressure')) {
                $suggestions[] = [
                    'icon' => 'fa-heart',
                    'color' => 'info',
                    'title' => $t('Hypertension Management', 'উচ্চ রক্তচাপ ব্যবস্থাপনা'),
                    'message' => $t("ACC/AHA 2017 guidelines recommend: Limit sodium to <1500mg/day, maintain DASH diet, exercise 90-150 min/week, limit alcohol, and monitor BP at home. Target BP <130/80 mmHg for most adults.",
                        "ACC/AHA ২০১৭ নির্দেশিকা সুপারিশ করে: সোডিয়াম প্রতিদিন <১৫০০mg সীমাবদ্ধ করুন, DASH ডায়েট বজায় রাখুন, সাপ্তাহিক ৯০-১৫০ মিনিট ব্যায়াম করুন, অ্যালকোহল সীমিত করুন এবং বাড়িতে BP পর্যবেক্ষণ করুন। বেশিরভাগ প্রাপ্তবয়স্কদের জন্য লক্ষ্য BP <১৩০/৮০ mmHg।"),
                    'category' => 'Condition',
                ];
                continue;
            }
            
            // Asthma Management (GINA Guidelines)
            if (str_contains($lowerName, 'asthma')) {
                $suggestions[] = [
                    'icon' => 'fa-lungs',
                    'color' => 'info',
                    'title' => $t('Asthma Care Tips', 'অ্যাজমা যত্ন টিপস'),
                    'message' => $t("GINA guidelines recommend: Use controller medication daily as prescribed, keep rescue inhaler accessible, avoid triggers (dust, smoke, pollen, cold air), and follow your written asthma action plan. Review your plan with your doctor every 6 months.",
                        "GINA নির্দেশিকা সুপারিশ করে: নির্ধারিত অনুযায়ী প্রতিদিন কন্ট্রোলার ওষুধ ব্যবহার করুন, রেসকিউ ইনহেলার সহজলভ্য রাখুন, ট্রিগার (ধুলো, ধোঁয়া, পরাগ, ঠান্ডা বাতাস) এড়িয়ে চলুন এবং আপনার লিখিত অ্যাজমা অ্যাকশন প্ল্যান অনুসরণ করুন। প্রতি ৬ মাসে আপনার ডাক্তারের সাথে আপনার পরিকল্পনা পর্যালোচনা করুন।"),
                    'category' => 'Condition',
                ];
                continue;
            }
        }

        // ═══════════════════════════════════════════════════════════════════
        // GETTING STARTED SUGGESTIONS (For new users with incomplete data)
        // ═══════════════════════════════════════════════════════════════════
        
        // No medicines tracked
        if ($medicines->isEmpty()) {
            $suggestions[] = [
                'icon' => 'fa-plus-circle',
                'color' => 'info',
                'title' => $t('Start Tracking Medicines', 'ঔষধ ট্র্যাক শুরু করুন'),
                'message' => $t("You haven't added any medicines yet. Add your prescriptions to get reminders and track adherence.",
                    "আপনি এখনও কোন ঔষধ যোগ করেননি। রিমাইন্ডার ও অনুসরণ ট্র্যাকিং পাওয়ার জন্য প্রেসক্রিপশন যোগ করুন।"),
                'category' => 'Getting Started',
            ];
        }

        // No metrics tracked
        if ($latestMetrics->isEmpty()) {
            $suggestions[] = [
                'icon' => 'fa-chart-line',
                'color' => 'info',
                'title' => $t('Record Health Metrics', 'স্বাস্থ্য মেট্রিক রেকর্ড করুন'),
                'message' => $t("Start recording your blood pressure, glucose, weight, and other metrics to get personalized health insights.",
                    "ব্যক্তিগত স্বাস্থ্য বিশ্লেষণের জন্য আপনার রক্তচাপ, গ্লুকোজ, ওজন ও অন্যান্য মেট্রিক রেকর্ড করা শুরু করুন।"),
                'category' => 'Getting Started',
            ];
        }

        // ═══════════════════════════════════════════════════════════════════
        // GENERAL WELLNESS SUGGESTIONS (Always displayed)
        // ═══════════════════════════════════════════════════════════════════
        
        $suggestions[] = [
            'icon' => 'fa-glass-water',
            'color' => 'primary',
            'title' => $t('Stay Hydrated', 'পর্যাপ্ত পানি পান করুন'),
            'message' => $t('Drink at least 8 glasses (2 litres) of water daily. Proper hydration supports digestion, skin health, and energy levels.',
                'প্রতিদিন অন্তত ৮ গ্লাস (২ লি) পানি পান করুন। পর্যাপ্ত জল শোষণ, ত্বক ও শক্তি স্তরকে সমর্থন করে।'),
            'category' => 'Wellness',
        ];

        $suggestions[] = [
            'icon' => 'fa-bed',
            'color' => 'primary',
            'title' => $t('Prioritize Sleep', 'ঘুমকে গুরুত্ব দিন'),
            'message' => $t('Aim for 7-9 hours of quality sleep. Maintain a consistent schedule and avoid screens 30 min before bed.',
                'প্রতিদিন ৭-৯ ঘন্টা ভালো ঘুম লক্ষ্য করুন। নিয়মিত ঘুমের সময় বজায় রাখুন এবং বিছানার ৩০ মিনিট আগে স্ক্রিন এড়িয়ে চলুন।'),
            'category' => 'Wellness',
        ];

        $suggestions[] = [
            'icon' => 'fa-user-clock',
            'color' => 'info',
            'title' => $t('Take Short Breaks', 'সংক্ষিপ্ত বিরতি নিন'),
            'message' => $t('If you work long hours, take a 5-10 minute break every hour to stretch and rest your eyes.',
                'যদি আপনি দীর্ঘ সময় কাজ করেন, প্রতি ঘন্টায় ৫-১০ মিনিট বিরতি নিন, স্ট্রেচ করুন এবং চোখকে বিশ্রাম দিন।'),
            'category' => 'Lifestyle',
        ];

        $suggestions[] = [
            'icon' => 'fa-apple-alt',
            'color' => 'info',
            'title' => $t('Balanced Diet Tip', 'সুষম ডায়েট টিপ'),
            'message' => $t('Include vegetables, lean protein, whole grains, and fruits in your daily meals for better long-term health.',
                'দীর্ঘকালীন সুস্থতার জন্য প্রতিদিনের খাবারে সবজি, লীন প্রোটিন, সস্তা শস্য ও ফল যোগ করুন।'),
            'category' => 'Lifestyle',
        ];

        return $suggestions;
    }

    /**
     * Ensure that default health metric definitions exist in the database.
     * Seeds default metrics if none are present.
     *
     * @return void
     */
    private function ensureMetricDefinitions(): void
    {
        HealthMetric::seedDefaults();
    }
}