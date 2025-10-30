<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Data\Recording;
use App\Models\Data\RecordingDetail;

class RecordingUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /**
     * Test basic recording upload
     */
    public function test_can_upload_recording(): void
    {
        $file = UploadedFile::fake()->create('test_recording.wav', 1024, 'audio/wav');

        $response = $this->post('/admin/recordings/upload', [
            'recording' => $file,
            'requires_ticket' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('recording_details', [
            'file_name' => 'test_recording.wav',
            'status' => 'pending',
        ]);
    }

    /**
     * Test recording upload with ticket requirement
     */
    public function test_can_upload_recording_with_ticket_requirement(): void
    {
        $file = UploadedFile::fake()->create('TICKET001_call.wav', 1024, 'audio/wav');

        $response = $this->post('/admin/recordings/upload', [
            'recording' => $file,
            'requires_ticket' => true,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('recording_details', [
            'file_name' => 'TICKET001_call.wav',
            'requires_ticket' => true,
        ]);
    }

    /**
     * Test recording upload validation
     */
    public function test_recording_upload_requires_file(): void
    {
        $response = $this->post('/admin/recordings/upload', [
            'requires_ticket' => false,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['recording']);
    }

    /**
     * Test recording upload with invalid file type
     */
    public function test_recording_upload_validates_file_type(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->post('/admin/recordings/upload', [
            'recording' => $file,
            'requires_ticket' => false,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test recording list endpoint
     */
    public function test_can_list_recordings(): void
    {
        RecordingDetail::factory()->count(5)->create();

        $response = $this->get('/admin/recordings');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /**
     * Test recording detail endpoint
     */
    public function test_can_view_recording_detail(): void
    {
        $recording = RecordingDetail::factory()->create([
            'file_name' => 'test.wav',
            'status' => 'completed',
        ]);

        $response = $this->get("/admin/recordings/{$recording->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $recording->id,
            'file_name' => 'test.wav',
        ]);
    }
}
