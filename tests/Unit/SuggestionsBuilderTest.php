<?php

namespace Tests\Unit;

use App\Http\Controllers\SuggestionsController;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Unit tests for SuggestionsController::buildSuggestions().
 *
 * Because buildSuggestions() is private we call it via ReflectionMethod.
 * No database or HTTP layer is involved: all inputs are plain Collection /
 * stdClass objects crafted inline for each scenario.
 */
class SuggestionsBuilderTest extends TestCase
{
    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    /** Invoke the private buildSuggestions method on a fresh controller. */
    private function build(
        Collection $metrics,
        Collection $symptoms,
        Collection $conditions,
        ?int       $adherenceRate,
        Collection $medicines
    ): array {
        $controller = new SuggestionsController();
        $method = new ReflectionMethod($controller, 'buildSuggestions');
        $method->setAccessible(true);
        return $method->invoke($controller, null, $metrics, $symptoms, $conditions, $adherenceRate, $medicines);
    }

    /** Fake metric object keyed by type. */
    private function metric(array $value): object
    {
        return (object) ['value' => $value];
    }

    /** Metrics map with a single entry. */
    private function metricsOf(string $type, array $value): Collection
    {
        return collect([$type => $this->metric($value)]);
    }

    /** Default non-empty collections so unrelated suggestions are not generated. */
    private function defaultMedicines(): Collection
    {
        return collect([(object) ['id' => 1]]);
    }

    private function defaultMetrics(): Collection
    {
        return collect(['blood_pressure' => $this->metric(['systolic' => 120, 'diastolic' => 80])]);
    }

    private function noConditions(): Collection { return collect(); }
    private function noSymptoms(): Collection   { return collect(); }

    /** Find first suggestion matching $title, or null. */
    private function find(array $suggestions, string $title): ?array
    {
        foreach ($suggestions as $s) {
            if ($s['title'] === $title) {
                return $s;
            }
        }
        return null;
    }

    // ──────────────────────────────────────────────────
    // Blood Pressure
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_high_bp_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('blood_pressure', ['systolic' => 145, 'diastolic' => 95]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'High Blood Pressure Detected');
        $this->assertNotNull($s, 'Expected high BP suggestion');
        $this->assertEquals('danger', $s['color']);
        $this->assertEquals('Metric Alert', $s['category']);
        $this->assertStringContainsString('145/95', $s['message']);
    }

    #[Test]
    public function it_generates_low_bp_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('blood_pressure', ['systolic' => 85, 'diastolic' => 55]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Low Blood Pressure');
        $this->assertNotNull($s, 'Expected low BP suggestion');
        $this->assertEquals('warning', $s['color']);
        $this->assertStringContainsString('85/55', $s['message']);
    }

    #[Test]
    public function it_does_not_generate_bp_suggestion_for_normal_values(): void
    {
        $result = $this->build(
            $this->metricsOf('blood_pressure', ['systolic' => 120, 'diastolic' => 80]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $this->assertNull($this->find($result, 'High Blood Pressure Detected'));
        $this->assertNull($this->find($result, 'Low Blood Pressure'));
    }

    // ──────────────────────────────────────────────────
    // Blood Glucose
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_high_glucose_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('blood_glucose', ['value' => 200]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'High Blood Sugar');
        $this->assertNotNull($s, 'Expected high glucose suggestion');
        $this->assertEquals('danger', $s['color']);
        $this->assertStringContainsString('200', $s['message']);
    }

    #[Test]
    public function it_generates_low_glucose_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('blood_glucose', ['value' => 60]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Low Blood Sugar');
        $this->assertNotNull($s, 'Expected low glucose suggestion');
        $this->assertEquals('warning', $s['color']);
        $this->assertStringContainsString('60', $s['message']);
    }

    // ──────────────────────────────────────────────────
    // Heart Rate
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_elevated_heart_rate_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('heart_rate', ['bpm' => 105]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Elevated Heart Rate');
        $this->assertNotNull($s, 'Expected elevated heart rate suggestion');
        $this->assertEquals('warning', $s['color']);
        $this->assertStringContainsString('105', $s['message']);
    }

    #[Test]
    public function it_generates_low_heart_rate_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('heart_rate', ['bpm' => 50]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Low Heart Rate');
        $this->assertNotNull($s, 'Expected low heart rate suggestion');
        $this->assertEquals('info', $s['color']);
    }

    #[Test]
    public function it_does_not_generate_heart_rate_suggestion_for_normal_bpm(): void
    {
        $result = $this->build(
            $this->metricsOf('heart_rate', ['bpm' => 72]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $this->assertNull($this->find($result, 'Elevated Heart Rate'));
        $this->assertNull($this->find($result, 'Low Heart Rate'));
    }

    // ──────────────────────────────────────────────────
    // Oxygen Saturation
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_low_oxygen_saturation_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('oxygen_saturation', ['value' => 92]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Low Oxygen Saturation');
        $this->assertNotNull($s);
        $this->assertEquals('danger', $s['color']);
        $this->assertStringContainsString('92', $s['message']);
    }

    #[Test]
    public function it_does_not_generate_spo2_suggestion_when_normal(): void
    {
        $result = $this->build(
            $this->metricsOf('oxygen_saturation', ['value' => 98]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $this->assertNull($this->find($result, 'Low Oxygen Saturation'));
    }

    // ──────────────────────────────────────────────────
    // BMI
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_obese_bmi_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('bmi', ['value' => 32]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'BMI Indicates Obesity');
        $this->assertNotNull($s);
        $this->assertEquals('warning', $s['color']);
        $this->assertEquals('Lifestyle', $s['category']);
    }

    #[Test]
    public function it_generates_overweight_bmi_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('bmi', ['value' => 27]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Overweight BMI');
        $this->assertNotNull($s);
        $this->assertEquals('info', $s['color']);
    }

    #[Test]
    public function it_generates_underweight_bmi_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('bmi', ['value' => 17]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Underweight BMI');
        $this->assertNotNull($s);
        $this->assertEquals('warning', $s['color']);
    }

    // ──────────────────────────────────────────────────
    // Temperature
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_fever_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('temperature', ['value' => 38.5]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Fever Detected');
        $this->assertNotNull($s);
        $this->assertEquals('danger', $s['color']);
        $this->assertStringContainsString('38.5', $s['message']);
    }

    #[Test]
    public function it_does_not_generate_fever_suggestion_for_normal_temp(): void
    {
        $result = $this->build(
            $this->metricsOf('temperature', ['value' => 36.8]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $this->assertNull($this->find($result, 'Fever Detected'));
    }

    // ──────────────────────────────────────────────────
    // Hemoglobin
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_low_hemoglobin_suggestion(): void
    {
        $result = $this->build(
            $this->metricsOf('hemoglobin', ['value' => 10.5]),
            $this->noSymptoms(), $this->noConditions(), null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Low Hemoglobin');
        $this->assertNotNull($s);
        $this->assertEquals('warning', $s['color']);
        $this->assertStringContainsString('10.5', $s['message']);
    }

    // ──────────────────────────────────────────────────
    // Adherence
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_very_low_adherence_suggestion(): void
    {
        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $this->noConditions(),
            40, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Very Low Medicine Adherence');
        $this->assertNotNull($s);
        $this->assertEquals('danger', $s['color']);
        $this->assertEquals('Adherence', $s['category']);
        $this->assertStringContainsString('40%', $s['message']);
    }

    #[Test]
    public function it_generates_low_adherence_suggestion(): void
    {
        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $this->noConditions(),
            70, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Improve Your Medicine Adherence');
        $this->assertNotNull($s);
        $this->assertEquals('warning', $s['color']);
        $this->assertStringContainsString('70%', $s['message']);
    }

    #[Test]
    public function it_generates_excellent_adherence_suggestion(): void
    {
        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $this->noConditions(),
            95, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Excellent Adherence!');
        $this->assertNotNull($s);
        $this->assertEquals('success', $s['color']);
        $this->assertStringContainsString('95%', $s['message']);
    }

    #[Test]
    public function it_does_not_generate_adherence_suggestion_when_rate_is_null(): void
    {
        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $this->noConditions(),
            null, $this->defaultMedicines()
        );

        $this->assertNull($this->find($result, 'Very Low Medicine Adherence'));
        $this->assertNull($this->find($result, 'Improve Your Medicine Adherence'));
        $this->assertNull($this->find($result, 'Excellent Adherence!'));
    }

    // ──────────────────────────────────────────────────
    // Symptoms
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_severe_symptoms_suggestion(): void
    {
        $severe = collect([
            (object) ['symptom_name' => 'chest_pain',   'severity_level' => 9],
            (object) ['symptom_name' => 'shortness_of_breath', 'severity_level' => 8],
        ]);

        $result = $this->build(
            $this->defaultMetrics(), $severe, $this->noConditions(),
            null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Severe Symptoms Reported');
        $this->assertNotNull($s);
        $this->assertEquals('danger', $s['color']);
        $this->assertEquals('Symptom', $s['category']);
    }

    #[Test]
    public function it_generates_multiple_symptoms_suggestion(): void
    {
        $symptoms = collect(array_map(
            fn($i) => (object) ['symptom_name' => "symptom_{$i}", 'severity_level' => 3],
            range(1, 6)
        ));

        $result = $this->build(
            $this->defaultMetrics(), $symptoms, $this->noConditions(),
            null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Multiple Symptoms Logged');
        $this->assertNotNull($s);
        $this->assertEquals('info', $s['color']);
        $this->assertStringContainsString('6 symptoms', $s['message']);
    }

    #[Test]
    public function it_does_not_generate_multiple_symptoms_suggestion_below_threshold(): void
    {
        $symptoms = collect([
            (object) ['symptom_name' => 'headache', 'severity_level' => 4],
            (object) ['symptom_name' => 'fatigue',  'severity_level' => 3],
        ]);

        $result = $this->build(
            $this->defaultMetrics(), $symptoms, $this->noConditions(),
            null, $this->defaultMedicines()
        );

        $this->assertNull($this->find($result, 'Multiple Symptoms Logged'));
    }

    // ──────────────────────────────────────────────────
    // Condition-based
    // ──────────────────────────────────────────────────

    private function condition(string $diseaseName): object
    {
        $disease = (object) ['disease_name' => $diseaseName];
        return (object) ['disease' => $disease];
    }

    #[Test]
    public function it_generates_diabetes_condition_suggestion(): void
    {
        $conditions = collect([$this->condition('Type 2 Diabetes')]);

        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $conditions,
            null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Diabetes Management Tips');
        $this->assertNotNull($s);
        $this->assertEquals('info', $s['color']);
        $this->assertEquals('Condition', $s['category']);
    }

    #[Test]
    public function it_generates_hypertension_condition_suggestion(): void
    {
        $conditions = collect([$this->condition('Hypertension')]);

        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $conditions,
            null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Hypertension Management');
        $this->assertNotNull($s);
        $this->assertEquals('Condition', $s['category']);
    }

    #[Test]
    public function it_generates_asthma_condition_suggestion(): void
    {
        $conditions = collect([$this->condition('Bronchial Asthma')]);

        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $conditions,
            null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Asthma Care Tips');
        $this->assertNotNull($s);
        $this->assertEquals('Condition', $s['category']);
    }

    #[Test]
    public function it_generates_multiple_condition_suggestions_when_applicable(): void
    {
        // Two separate active conditions each matching a different rule
        $conditions = collect([
            $this->condition('Type 2 Diabetes'),
            $this->condition('Hypertension'),
        ]);

        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $conditions,
            null, $this->defaultMedicines()
        );

        $this->assertNotNull($this->find($result, 'Diabetes Management Tips'));
        $this->assertNotNull($this->find($result, 'Hypertension Management'));
    }

    // ──────────────────────────────────────────────────
    // Getting Started
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_generates_getting_started_suggestion_when_no_medicines(): void
    {
        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $this->noConditions(),
            null, collect() // no medicines
        );

        $s = $this->find($result, 'Start Tracking Medicines');
        $this->assertNotNull($s);
        $this->assertEquals('Getting Started', $s['category']);
        $this->assertEquals('info', $s['color']);
    }

    #[Test]
    public function it_generates_getting_started_suggestion_when_no_metrics(): void
    {
        $result = $this->build(
            collect(), // no metrics
            $this->noSymptoms(), $this->noConditions(),
            null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Record Health Metrics');
        $this->assertNotNull($s);
        $this->assertEquals('Getting Started', $s['category']);
    }

    #[Test]
    public function it_does_not_add_medicines_getting_started_when_medicines_exist(): void
    {
        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $this->noConditions(),
            null, $this->defaultMedicines() // medicines present
        );

        $this->assertNull($this->find($result, 'Start Tracking Medicines'));
    }

    // ──────────────────────────────────────────────────
    // Wellness (always present)
    // ──────────────────────────────────────────────────

    #[Test]
    public function it_always_generates_hydration_wellness_suggestion(): void
    {
        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $this->noConditions(),
            null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Stay Hydrated');
        $this->assertNotNull($s);
        $this->assertEquals('Wellness', $s['category']);
        $this->assertEquals('primary', $s['color']);
    }

    #[Test]
    public function it_always_generates_sleep_wellness_suggestion(): void
    {
        $result = $this->build(
            $this->defaultMetrics(), $this->noSymptoms(), $this->noConditions(),
            null, $this->defaultMedicines()
        );

        $s = $this->find($result, 'Prioritize Sleep');
        $this->assertNotNull($s);
        $this->assertEquals('Wellness', $s['category']);
    }

    #[Test]
    public function wellness_suggestions_are_present_even_with_no_other_data(): void
    {
        // Completely bare slate
        $result = $this->build(
            collect(), collect(), collect(), null, collect()
        );

        $categories = array_column($result, 'category');
        $this->assertContains('Wellness', $categories);

        $wellnessCount = count(array_filter($categories, fn($c) => $c === 'Wellness'));
        $this->assertEquals(2, $wellnessCount, 'Expected exactly 2 wellness suggestions (hydration + sleep)');
    }

    // ──────────────────────────────────────────────────
    // Suggestion structure
    // ──────────────────────────────────────────────────

    #[Test]
    public function every_suggestion_has_required_keys(): void
    {
        $result = $this->build(
            $this->metricsOf('blood_pressure', ['systolic' => 150, 'diastolic' => 100]),
            collect([
                (object) ['symptom_name' => 'headache', 'severity_level' => 8],
            ]),
            collect([$this->condition('Diabetes')]),
            40,
            collect()
        );

        $this->assertNotEmpty($result);
        foreach ($result as $suggestion) {
            $this->assertArrayHasKey('icon',     $suggestion);
            $this->assertArrayHasKey('color',    $suggestion);
            $this->assertArrayHasKey('title',    $suggestion);
            $this->assertArrayHasKey('message',  $suggestion);
            $this->assertArrayHasKey('category', $suggestion);
        }
    }
}
