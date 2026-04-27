<?php

namespace Tests\Unit;

use App\Http\Controllers\SuggestionsController;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Boundary-value / equivalence-partition tests for buildSuggestions().
 *
 * For every threshold in the controller we test:
 *  – one value that just crosses into a trigger zone  (T)
 *  – the exact boundary value itself                  (B)
 *  – one value that sits just outside the trigger zone (O)
 *
 * No database is used — all data is constructed inline.
 */
class SuggestionsBoundaryTest extends TestCase
{
    // ──────────────────────────────────────────────────
    // Infrastructure (same helpers as SuggestionsBuilderTest)
    // ──────────────────────────────────────────────────

    private function build(
        Collection $metrics,
        Collection $symptoms,
        Collection $conditions,
        ?int $adherenceRate,
        Collection $medicines
    ): array {
        $ctrl   = new SuggestionsController();
        $method = new ReflectionMethod($ctrl, 'buildSuggestions');
        $method->setAccessible(true);
        return $method->invoke($ctrl, null, $metrics, $symptoms, $conditions, $adherenceRate, $medicines);
    }

    private function metricOf(string $type, array $value): Collection
    {
        return collect([$type => (object) ['value' => $value]]);
    }

    private function defaultMeds(): Collection
    {
        return collect([(object) ['id' => 1]]);
    }

    private function defaultMet(): Collection
    {
        return $this->metricOf('blood_pressure', ['systolic' => 120, 'diastolic' => 80]);
    }

    private function noC(): Collection { return collect(); }
    private function noS(): Collection { return collect(); }

    private function has(array $suggestions, string $title): bool
    {
        return collect($suggestions)->contains('title', $title);
    }

    // ──────────────────────────────────────────────────
    // Blood Pressure – systolic (threshold >= 140)
    // ──────────────────────────────────────────────────

    #[Test]
    public function bp_systolic_140_is_high(): void   // at-boundary → triggers
    {
        $result = $this->build(
            $this->metricOf('blood_pressure', ['systolic' => 140, 'diastolic' => 80]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'High Blood Pressure (Stage 2)'));
    }

    #[Test]
    public function bp_systolic_139_is_not_high(): void   // just outside
    {
        $result = $this->build(
            $this->metricOf('blood_pressure', ['systolic' => 139, 'diastolic' => 80]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        // Stage 1 starts at 130, so 139 should be Stage 1
        $this->assertTrue($this->has($result, 'High Blood Pressure (Stage 1)'));
    }

    // ── diastolic (threshold >= 90) ──

    #[Test]
    public function bp_diastolic_90_is_high(): void
    {
        $result = $this->build(
            $this->metricOf('blood_pressure', ['systolic' => 120, 'diastolic' => 90]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'High Blood Pressure (Stage 2)'));
    }

    #[Test]
    public function bp_diastolic_89_is_not_high(): void
    {
        $result = $this->build(
            $this->metricOf('blood_pressure', ['systolic' => 120, 'diastolic' => 89]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'High Blood Pressure (Stage 2)'));
        $this->assertTrue($this->has($result, 'High Blood Pressure (Stage 1)'));
    }

    // ── lower bound (systolic < 90) ──

    #[Test]
    public function bp_systolic_89_triggers_low_bp(): void
    {
        $result = $this->build(
            $this->metricOf('blood_pressure', ['systolic' => 89, 'diastolic' => 70]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Low Blood Pressure (Hypotension)'));
    }

    #[Test]
    public function bp_systolic_90_does_not_trigger_low_bp(): void
    {
        $result = $this->build(
            $this->metricOf('blood_pressure', ['systolic' => 90, 'diastolic' => 70]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Low Blood Pressure (Hypotension)'));
    }

    // ──────────────────────────────────────────────────
    // Blood Glucose (high: > 180 | low: < 70)
    // ──────────────────────────────────────────────────

    #[Test]
    public function glucose_181_is_high(): void
    {
        $result = $this->build(
            $this->metricOf('blood_glucose', ['value' => 181, 'context' => 'random']),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'High Blood Sugar (Hyperglycemia)'));
    }

    #[Test]
    public function glucose_180_is_not_high(): void   // boundary — NOT strictly > 180
    {
        $result = $this->build(
            $this->metricOf('blood_glucose', ['value' => 180, 'context' => 'random']),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'High Blood Sugar (Hyperglycemia)'));
    }

    #[Test]
    public function glucose_69_is_low(): void
    {
        $result = $this->build(
            $this->metricOf('blood_glucose', ['value' => 69]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Low Blood Sugar (Hypoglycemia)'));
    }

    #[Test]
    public function glucose_70_is_not_low(): void   // boundary — NOT strictly < 70
    {
        $result = $this->build(
            $this->metricOf('blood_glucose', ['value' => 70]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Low Blood Sugar (Hypoglycemia)'));
    }

    // ──────────────────────────────────────────────────
    // Heart Rate (high: > 100 | low: < 60)
    // ──────────────────────────────────────────────────

    #[Test]
    public function heart_rate_101_is_elevated(): void
    {
        $result = $this->build(
            $this->metricOf('heart_rate', ['bpm' => 101]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Elevated Heart Rate (Tachycardia)'));
    }

    #[Test]
    public function heart_rate_100_is_not_elevated(): void
    {
        $result = $this->build(
            $this->metricOf('heart_rate', ['bpm' => 100]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Elevated Heart Rate (Tachycardia)'));
    }

    #[Test]
    public function heart_rate_59_is_low(): void
    {
        $result = $this->build(
            $this->metricOf('heart_rate', ['bpm' => 59]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Low Heart Rate (Bradycardia)'));
    }

    #[Test]
    public function heart_rate_60_is_not_low(): void
    {
        $result = $this->build(
            $this->metricOf('heart_rate', ['bpm' => 60]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Low Heart Rate (Bradycardia)'));
    }

    // ──────────────────────────────────────────────────
    // Oxygen Saturation (low: < 95)
    // ──────────────────────────────────────────────────

    #[Test]
    public function spo2_94_triggers_low_alert(): void
    {
        $result = $this->build(
            $this->metricOf('oxygen_saturation', ['value' => 94]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Monitor Oxygen Level'));
    }

    #[Test]
    public function spo2_95_does_not_trigger_alert(): void
    {
        $result = $this->build(
            $this->metricOf('oxygen_saturation', ['value' => 95]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Monitor Oxygen Level'));
    }

    // ──────────────────────────────────────────────────
    // BMI (obese: >= 30 | overweight: >= 25 & < 30 | underweight: < 18.5)
    // ──────────────────────────────────────────────────

    #[Test]
    public function bmi_30_triggers_obesity(): void   // boundary — exactly 30 is obese
    {
        $result = $this->build(
            $this->metricOf('bmi', ['value' => 30]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Obesity (Class I)'));
        $this->assertFalse($this->has($result, 'Overweight'));
    }

    #[Test]
    public function bmi_29_9_triggers_overweight_not_obesity(): void
    {
        $result = $this->build(
            $this->metricOf('bmi', ['value' => 29.9]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Obesity (Class I)'));
        $this->assertTrue($this->has($result, 'Overweight'));
    }

    #[Test]
    public function bmi_25_triggers_overweight(): void   // lower bound of overweight zone
    {
        $result = $this->build(
            $this->metricOf('bmi', ['value' => 25]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Overweight'));
        $this->assertFalse($this->has($result, 'Obesity (Class I)'));
    }

    #[Test]
    public function bmi_24_9_is_normal(): void
    {
        $result = $this->build(
            $this->metricOf('bmi', ['value' => 24.9]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Overweight'));
        $this->assertFalse($this->has($result, 'Obesity (Class I)'));
        $this->assertFalse($this->has($result, 'Underweight'));
    }

    #[Test]
    public function bmi_18_5_is_normal(): void  // exactly at underweight threshold — NOT underweight (< 18.5)
    {
        $result = $this->build(
            $this->metricOf('bmi', ['value' => 18.5]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Underweight'));
    }

    #[Test]
    public function bmi_18_4_triggers_underweight(): void
    {
        $result = $this->build(
            $this->metricOf('bmi', ['value' => 18.4]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Underweight'));
    }

    // ──────────────────────────────────────────────────
    // Temperature (fever: >= 38)
    // ──────────────────────────────────────────────────

    #[Test]
    public function temperature_38_triggers_fever(): void    // exactly at boundary
    {
        $result = $this->build(
            $this->metricOf('temperature', ['value' => 38.0]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Fever Detected'));
    }

    #[Test]
    public function temperature_37_9_does_not_trigger_fever(): void
    {
        $result = $this->build(
            $this->metricOf('temperature', ['value' => 37.9]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Fever Detected'));
    }

    // ──────────────────────────────────────────────────
    // Hemoglobin (low: < 12)
    // ──────────────────────────────────────────────────

    #[Test]
    public function hemoglobin_11_9_triggers_low_alert(): void
    {
        $result = $this->build(
            $this->metricOf('hemoglobin', ['value' => 11.9]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Low Hemoglobin (Anemia)'));
    }

    #[Test]
    public function hemoglobin_12_does_not_trigger_alert(): void
    {
        $result = $this->build(
            $this->metricOf('hemoglobin', ['value' => 12.0]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Low Hemoglobin (Anemia)'));
    }

    // ──────────────────────────────────────────────────
    // Adherence thresholds
    // ──────────────────────────────────────────────────

    #[Test]
    public function adherence_49_is_very_low(): void
    {
        $result = $this->build(
            $this->defaultMet(), $this->noS(), $this->noC(), 49, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Critical Medicine Adherence'));
        $this->assertFalse($this->has($result, 'Improve Medicine Adherence'));
    }

    #[Test]
    public function adherence_50_is_improve_zone_not_very_low(): void
    {
        $result = $this->build(
            $this->defaultMet(), $this->noS(), $this->noC(), 50, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Critical Medicine Adherence'));
        $this->assertTrue($this->has($result, 'Improve Medicine Adherence'));
    }

    #[Test]
    public function adherence_79_is_improve_zone(): void
    {
        $result = $this->build(
            $this->defaultMet(), $this->noS(), $this->noC(), 79, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Improve Medicine Adherence'));
        $this->assertFalse($this->has($result, 'Excellent Adherence!'));
    }

    #[Test]
    public function adherence_80_is_neutral_zone(): void  // not < 80, not >= 90 → no alert
    {
        $result = $this->build(
            $this->defaultMet(), $this->noS(), $this->noC(), 80, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Critical Medicine Adherence'));
        $this->assertFalse($this->has($result, 'Improve Medicine Adherence'));
        $this->assertFalse($this->has($result, 'Excellent Adherence!'));
    }

    #[Test]
    public function adherence_89_is_neutral_zone(): void
    {
        $result = $this->build(
            $this->defaultMet(), $this->noS(), $this->noC(), 89, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Critical Medicine Adherence'));
        $this->assertFalse($this->has($result, 'Improve Medicine Adherence'));
        $this->assertFalse($this->has($result, 'Excellent Adherence!'));
    }

    #[Test]
    public function adherence_90_triggers_excellent(): void  // exactly at boundary
    {
        $result = $this->build(
            $this->defaultMet(), $this->noS(), $this->noC(), 90, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Improve Medicine Adherence'));
        $this->assertTrue($this->has($result, 'Excellent Adherence!'));
    }

    #[Test]
    public function adherence_0_is_very_low(): void   // extreme lower bound
    {
        $result = $this->build(
            $this->defaultMet(), $this->noS(), $this->noC(), 0, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Critical Medicine Adherence'));
    }

    #[Test]
    public function adherence_100_triggers_excellent(): void  // extreme upper bound
    {
        $result = $this->build(
            $this->defaultMet(), $this->noS(), $this->noC(), 100, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Excellent Adherence!'));
    }

    // ──────────────────────────────────────────────────
    // Symptom boundaries
    // ──────────────────────────────────────────────────

    private function makeSymptoms(int $count, int $severity): Collection
    {
        return collect(array_map(
            fn($i) => (object) ['symptom_name' => "s{$i}", 'severity_level' => $severity],
            range(1, $count)
        ));
    }

    #[Test]
    public function exactly_5_symptoms_triggers_multiple_suggestion(): void
    {
        $result = $this->build(
            $this->defaultMet(), $this->makeSymptoms(5, 3), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Multiple Symptoms Logged'));
    }

    #[Test]
    public function exactly_4_symptoms_does_not_trigger_multiple_suggestion(): void
    {
        $result = $this->build(
            $this->defaultMet(), $this->makeSymptoms(4, 3), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Multiple Symptoms Logged'));
    }

    #[Test]
    public function severity_7_triggers_severe_suggestion(): void   // at boundary
    {
        $result = $this->build(
            $this->defaultMet(),
            collect([(object) ['symptom_name' => 'pain', 'severity_level' => 7]]),
            $this->noC(), null, $this->defaultMeds()
        );

        $this->assertTrue($this->has($result, 'Severe Symptoms Reported'));
    }

    #[Test]
    public function severity_6_does_not_trigger_severe_suggestion(): void
    {
        $result = $this->build(
            $this->defaultMet(),
            collect([(object) ['symptom_name' => 'pain', 'severity_level' => 6]]),
            $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Severe Symptoms Reported'));
    }

    // ──────────────────────────────────────────────────
    // Null / missing metric value fields
    // ──────────────────────────────────────────────────

    #[Test]
    public function missing_systolic_in_bp_value_does_not_crash(): void
    {
        $result = $this->build(
            collect(['blood_pressure' => (object) ['value' => ['diastolic' => 95]]]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        // No suggestion should fire because systolic is missing
        $this->assertFalse($this->has($result, 'High Blood Pressure (Stage 2)'));
        $this->assertFalse($this->has($result, 'Low Blood Pressure (Hypotension)'));
    }

    #[Test]
    public function missing_glucose_value_field_does_not_crash(): void
    {
        $result = $this->build(
            collect(['blood_glucose' => (object) ['value' => []]]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'High Blood Sugar (Hyperglycemia)'));
        $this->assertFalse($this->has($result, 'Low Blood Sugar (Hypoglycemia)'));
    }

    #[Test]
    public function missing_bpm_in_heart_rate_does_not_crash(): void
    {
        $result = $this->build(
            collect(['heart_rate' => (object) ['value' => []]]),
            $this->noS(), $this->noC(), null, $this->defaultMeds()
        );

        $this->assertFalse($this->has($result, 'Elevated Heart Rate (Tachycardia)'));
        $this->assertFalse($this->has($result, 'Low Heart Rate (Bradycardia)'));
    }
}