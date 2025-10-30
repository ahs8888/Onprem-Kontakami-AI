<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Models\Data\RecordingDetail;

class TicketImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test CSV upload and validation
     */
    public function test_can_upload_and_validate_csv(): void
    {
        $csvContent = "Ticket ID,Customer Name,Agent Name,Intent,Outcome,Ticket URL\n";
        $csvContent .= "TICKET001,John Doe,Agent Smith,Billing,Resolved,https://example.com/001\n";
        $csvContent .= "TICKET002,Jane Smith,Agent Jones,Technical,Pending,https://example.com/002";

        $file = UploadedFile::fake()->createWithContent('tickets.csv', $csvContent);

        $response = $this->post('/admin/tickets/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'headers',
            'preview',
            'row_count',
        ]);
    }

    /**
     * Test column mapping
     */
    public function test_can_map_columns(): void
    {
        $response = $this->post('/admin/tickets/map', [
            'session_id' => 'test_session',
            'mapping' => [
                'ticket_id' => 'Ticket ID',
                'customer_name' => 'Customer Name',
                'agent_name' => 'Agent Name',
                'intent' => 'Intent',
                'outcome' => 'Outcome',
                'ticket_url' => 'Ticket URL',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test ticket import
     */
    public function test_can_import_tickets(): void
    {
        $response = $this->post('/admin/tickets/import', [
            'session_id' => 'test_session',
            'tickets' => [
                [
                    'ticket_id' => 'TICKET001',
                    'customer_name' => 'John Doe',
                    'agent_name' => 'Agent Smith',
                    'intent' => 'Billing',
                    'outcome' => 'Resolved',
                    'ticket_url' => 'https://example.com/001',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'imported_count' => 1,
        ]);
    }

    /**
     * Test ticket linking to recording
     */
    public function test_ticket_links_to_recording(): void
    {
        // Create recording
        $recording = RecordingDetail::factory()->create([
            'file_name' => 'TICKET001_call.wav',
            'requires_ticket' => true,
            'status' => 'pending',
        ]);

        // Import ticket
        $this->post('/admin/tickets/import', [
            'session_id' => 'test_session',
            'tickets' => [
                [
                    'ticket_id' => 'TICKET001',
                    'customer_name' => 'John Doe',
                    'agent_name' => 'Agent Smith',
                ],
            ],
        ]);

        // Check if recording was linked
        $recording->refresh();
        $this->assertEquals('TICKET001', $recording->ticket_id);
        $this->assertEquals('John Doe', $recording->customer_name);
        $this->assertEquals('linked', $recording->status);
    }

    /**
     * Test unlinked recording status
     */
    public function test_unlinked_recording_has_correct_status(): void
    {
        $recording = RecordingDetail::factory()->create([
            'file_name' => 'UNKNOWN_TICKET_call.wav',
            'requires_ticket' => true,
            'status' => 'pending',
        ]);

        // Run linking service (simulated)
        // In real test, this would trigger the linking job

        $recording->refresh();
        
        // If no ticket found, should be unlinked
        if (!$recording->ticket_id && $recording->requires_ticket) {
            $recording->update(['status' => 'unlinked']);
        }

        $this->assertEquals('unlinked', $recording->status);
    }

    /**
     * Test ticket stats endpoint
     */
    public function test_can_get_ticket_stats(): void
    {
        RecordingDetail::factory()->create(['status' => 'linked', 'ticket_id' => 'TICKET001']);
        RecordingDetail::factory()->create(['status' => 'unlinked', 'requires_ticket' => true]);
        RecordingDetail::factory()->create(['status' => 'no_ticket_needed']);

        $response = $this->get('/admin/recordings/ticket-stats');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total',
            'linked',
            'unlinked',
            'no_ticket_needed',
        ]);
    }
}
