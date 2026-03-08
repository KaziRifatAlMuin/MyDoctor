<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Translation;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_allOfType_falls_back_to_config_when_db_empty()
    {
        $symptomsConfig = config('health.symptoms');

        $result = Translation::allOfType(Translation::TYPE_SYMPTOM);

        $this->assertIsArray($result);
        $this->assertEquals($symptomsConfig, $result);
    }

    public function test_allOfType_prefers_database_values_when_present()
    {
        Translation::create(['type' => Translation::TYPE_SYMPTOM, 'key' => 'TestSymptom', 'value' => 'টেস্ট']);

        $result = Translation::allOfType(Translation::TYPE_SYMPTOM);

        $this->assertArrayHasKey('TestSymptom', $result);
        $this->assertEquals('টেস্ট', $result['TestSymptom']);
    }

    public function test_banglaFor_returns_value_or_fallback()
    {
        $fallback = 'fallback';
        $this->assertEquals($fallback, Translation::banglaFor(Translation::TYPE_METRIC, 'nope', $fallback));

        Translation::create(['type' => Translation::TYPE_METRIC, 'key' => 'my_metric', 'value' => 'বাংলা-মেট্রিক']);
        $this->assertEquals('বাংলা-মেট্রিক', Translation::banglaFor(Translation::TYPE_METRIC, 'my_metric', $fallback));
    }
}
