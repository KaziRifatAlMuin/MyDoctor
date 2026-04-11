<?php

namespace Tests\Feature\Database;

use App\Models\Disease;
use App\Models\Symptom;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CriticalSchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function newly_added_health_tables_have_critical_columns(): void
    {
        $this->assertTrue(Schema::hasTable('health_metrics'));
        $this->assertTrue(Schema::hasColumns('health_metrics', [
            'metric_name', 'fields',
        ]));

        $this->assertTrue(Schema::hasTable('user_health'));
        $this->assertTrue(Schema::hasColumns('user_health', [
            'user_id', 'health_metric_id', 'value', 'recorded_at',
        ]));

        $this->assertTrue(Schema::hasTable('medicine_reminders'));
        $this->assertTrue(Schema::hasColumns('medicine_reminders', [
            'schedule_id', 'reminder_at', 'status', 'taken_at',
        ]));

        $this->assertTrue(Schema::hasTable('diseases'));
        $this->assertTrue(Schema::hasColumns('diseases', [
            'disease_name', 'description',
        ]));

        $this->assertTrue(Schema::hasTable('symptoms'));
        $this->assertTrue(Schema::hasColumns('symptoms', [
            'name',
        ]));

        $this->assertTrue(Schema::hasTable('user_symptoms'));
        $this->assertTrue(Schema::hasColumns('user_symptoms', [
            'user_id', 'symptom_id', 'severity_level', 'note', 'recorded_at',
        ]));

        $this->assertTrue(Schema::hasTable('disease_symptoms'));
        $this->assertTrue(Schema::hasColumns('disease_symptoms', [
            'disease_id', 'symptom_id',
        ]));
    }

    #[Test]
    public function user_symptoms_enforces_foreign_keys_and_cascade_delete(): void
    {
        $user = User::factory()->create();
        $symptom = Symptom::factory()->create();

        DB::table('user_symptoms')->insert([
            'user_id' => $user->id,
            'symptom_id' => $symptom->id,
            'severity_level' => 5,
            'note' => 'Initial symptom note',
            'recorded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseCount('user_symptoms', 1);

        $user->delete();

        $this->assertDatabaseCount('user_symptoms', 0);
    }

    #[Test]
    public function user_symptoms_rejects_invalid_foreign_keys(): void
    {
        $this->expectException(QueryException::class);

        DB::table('user_symptoms')->insert([
            'user_id' => 999999,
            'symptom_id' => 999999,
            'severity_level' => 4,
            'recorded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    #[Test]
    public function disease_symptoms_prevents_duplicate_pairs(): void
    {
        $disease = Disease::factory()->create();
        $symptom = Symptom::factory()->create();

        DB::table('disease_symptoms')->insert([
            'disease_id' => $disease->id,
            'symptom_id' => $symptom->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('disease_symptoms')->insert([
            'disease_id' => $disease->id,
            'symptom_id' => $symptom->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
