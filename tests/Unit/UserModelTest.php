<?php

namespace Tests\Unit;

use App\Models\UserHealth;
use App\Models\Medicine;
use App\Models\MedicineLog;
use App\Models\User;
use App\Models\UserSymptom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_has_correct_fillable_attributes(): void
    {
        $expected = [
            'picture',
            'name',
            'date_of_birth',
            'phone',
            'email',
            'role',
            'occupation',
            'blood_group',
            'gender',
            'is_active',
            'password',
            'notification_settings',
            'starred_disease_ids',
            'starred_disease_history',
        ];
        
        $this->assertEquals($expected, (new User())->getFillable());
    }

    #[Test]
    public function user_hides_sensitive_attributes(): void
    {
        $hidden = (new User())->getHidden();
        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    #[Test]
    public function user_casts_date_of_birth_to_date(): void
    {
        $casts = (new User())->getCasts();
        $this->assertArrayHasKey('date_of_birth', $casts);
        $this->assertEquals('date', $casts['date_of_birth']);
    }

    #[Test]
    public function user_casts_password_as_hashed(): void
    {
        $casts = (new User())->getCasts();
        $this->assertArrayHasKey('password', $casts);
        $this->assertEquals('hashed', $casts['password']);
    }

    #[Test]
    public function user_casts_is_active_to_boolean(): void
    {
        $casts = (new User())->getCasts();
        $this->assertArrayHasKey('is_active', $casts);
        $this->assertEquals('boolean', $casts['is_active']);
    }

    #[Test]
    public function user_casts_notification_settings_to_array(): void
    {
        $casts = (new User())->getCasts();
        $this->assertArrayHasKey('notification_settings', $casts);
        $this->assertEquals('array', $casts['notification_settings']);
    }

    #[Test]
    public function user_can_be_created_in_database(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User'
        ]);
    }

    #[Test]
    public function user_has_health_metrics_relationship(): void
    {
        $user = User::factory()->create();
        UserHealth::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->healthMetrics());
        $this->assertEquals(1, $user->healthMetrics()->count());
    }

    #[Test]
    public function user_has_symptoms_relationship(): void
    {
        $user = User::factory()->create();
        UserSymptom::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->symptoms());
        $this->assertEquals(1, $user->symptoms()->count());
    }

    #[Test]
    public function user_has_medicines_relationship(): void
    {
        $user = User::factory()->create();
        Medicine::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->medicines());
        $this->assertEquals(1, $user->medicines()->count());
    }

    #[Test]
    public function user_has_medicine_logs_relationship(): void
    {
        $user = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        MedicineLog::factory()->create([
            'user_id' => $user->id,
            'medicine_id' => $medicine->id
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->medicineLogs());
        $this->assertEquals(1, $user->medicineLogs()->count());
    }

    #[Test]
    public function deleting_user_cascades_health_metrics(): void
    {
        $user = User::factory()->create();
        UserHealth::factory()->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('user_health', ['user_id' => $user->id]);
    }

    #[Test]
    public function deleting_user_cascades_symptoms(): void
    {
        $user = User::factory()->create();
        UserSymptom::factory()->count(3)->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('user_symptoms', ['user_id' => $user->id]);
    }

    #[Test]
    public function deleting_user_cascades_medicines(): void
    {
        $user = User::factory()->create();
        Medicine::factory()->count(2)->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('medicines', ['user_id' => $user->id]);
    }

    #[Test]
    public function deleting_user_cascades_medicine_logs(): void
    {
        $user     = User::factory()->create();
        $medicine = Medicine::factory()->create(['user_id' => $user->id]);
        MedicineLog::factory()->create([
            'user_id'     => $user->id,
            'medicine_id' => $medicine->id,
        ]);

        $user->delete();

        $this->assertDatabaseMissing('medicine_logs', ['user_id' => $user->id]);
    }
}