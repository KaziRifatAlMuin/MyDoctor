<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_prescription_and_file_saved()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // Use create() with mime type to avoid requiring GD extension in CI/local
        $file = UploadedFile::fake()->create('prescription.jpg', 150, 'image/jpeg');

        $payload = [
            'title' => 'Test Prescription',
            'type' => 'prescription',
            'file' => $file,
            'summary' => 'Integration upload test',
        ];

        $response = $this->actingAs($user)->post(route('health.upload.store'), $payload);
        $response->assertRedirect(route('health') . '#prescriptions');

        $this->assertDatabaseHas('uploads', [
            'user_id' => $user->id,
            'title' => 'Test Prescription',
            'type' => 'prescription',
        ]);

        $record = \App\Models\Upload::first();
        $this->assertNotNull($record->file_path);
        Storage::disk('public')->assertExists($record->file_path);
    }
}
