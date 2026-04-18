<?php

namespace Tests\Unit;

use App\Models\Disease;
use App\Models\Symptom;
use App\Models\UserSymptom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SymptomModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function symptom_has_correct_fillable_attributes(): void
    {
        $expected = ['name', 'name_bn', 'bangla_name'];
        $this->assertEquals($expected, (new Symptom())->getFillable());
    }

    #[Test]
    public function symptom_persists_bangla_name_alias_to_supported_columns(): void
    {
        $symptom = Symptom::create([
            'name' => 'cough_alias_test',
            'bangla_name' => 'কাশি',
        ]);

        $symptom->refresh();

        $this->assertSame('কাশি', $symptom->name_bn);
        $this->assertSame('কাশি', $symptom->bangla_name);
    }

    #[Test]
    public function symptom_extracts_inline_bangla_from_name_on_save(): void
    {
        $symptom = Symptom::create([
            'name' => 'fever_inline_test (জ্বর)',
        ]);

        $symptom->refresh();

        $this->assertSame('fever_inline_test', $symptom->name);
        $this->assertSame('জ্বর', $symptom->name_bn);
    }

    #[Test]
    public function symptom_has_timestamps_disabled(): void
    {
        $this->assertFalse((new Symptom())->timestamps);
    }

    #[Test]
    public function symptom_has_many_user_symptom_logs(): void
    {
        $symptom = Symptom::factory()->create();
        UserSymptom::factory()->create(['symptom_id' => $symptom->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $symptom->userSymptoms());
        $this->assertCount(1, $symptom->userSymptoms);
    }

    #[Test]
    public function symptom_belongs_to_many_diseases_via_pivot(): void
    {
        $symptom = Symptom::factory()->create();
        $disease = Disease::factory()->create();

        $symptom->diseases()->attach($disease->id);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $symptom->diseases());
        $this->assertTrue($symptom->diseases->contains($disease));
    }

    #[Test]
    public function symptom_name_is_unique(): void
    {
        $symptom = Symptom::factory()->create(['name' => 'migraine_unique']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Symptom::factory()->create(['name' => $symptom->name]);
    }

    #[Test]
    public function symptoms_can_be_queried_by_name(): void
    {
        Symptom::factory()->create(['name' => 'headache_query']);
        Symptom::factory()->create(['name' => 'fever_query']);

        $this->assertEquals(1, Symptom::where('name', 'headache_query')->count());
    }
}
