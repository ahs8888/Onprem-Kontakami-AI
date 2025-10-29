<?php

namespace App\Services;

use App\Models\Data\RecordingDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service for handling ticket linking operations
 */
class TicketLinkingService
{
    /**
     * Generate human-readable display name from ticket information.
     *
     * @param string|null $ticketId
     * @param string|null $customerName
     * @param string|null $callIntent
     * @param Carbon $date
     * @return string
     */
    public function generateDisplayName(
        ?string $ticketId, 
        ?string $customerName, 
        ?string $callIntent, 
        Carbon $date
    ): string {
        $parts = array_filter([
            $ticketId,
            $customerName,
            $callIntent,
            $date->format('Y-m-d H:i')
        ]);
        
        if (empty($parts)) {
            return 'Recording - ' . $date->format('Y-m-d H:i');
        }
        
        return implode(' - ', $parts);
    }

    /**
     * Link a single recording to ticket information.
     *
     * @param RecordingDetail $recording
     * @param array $ticketData
     * @return RecordingDetail
     */
    public function linkRecordingToTicket(RecordingDetail $recording, array $ticketData): RecordingDetail
    {
        $displayName = $this->generateDisplayName(
            $ticketData['ticket_id'] ?? null,
            $ticketData['customer_name'] ?? null,
            $ticketData['call_intent'] ?? null,
            $recording->created_at
        );

        $recording->update([
            'ticket_id' => $ticketData['ticket_id'] ?? null,
            'ticket_url' => $ticketData['ticket_url'] ?? null,
            'customer_name' => $ticketData['customer_name'] ?? null,
            'agent_name' => $ticketData['agent_name'] ?? null,
            'call_intent' => $ticketData['call_intent'] ?? null,
            'call_outcome' => $ticketData['call_outcome'] ?? null,
            'display_name' => $displayName,
            'status' => 'linked',
            'linked_at' => now(),
        ]);

        Log::info("Recording linked to ticket", [
            'recording_id' => $recording->id,
            'ticket_id' => $ticketData['ticket_id'] ?? null
        ]);

        return $recording->fresh();
    }

    /**
     * Parse CSV file and return structured data.
     *
     * @param string $filePath
     * @return array
     * @throws \Exception
     */
    public function parseCSV(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        $rows = [];
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            throw new \Exception("Unable to open file: {$filePath}");
        }
        
        // Get headers from first row
        $headers = fgetcsv($handle);
        
        if ($headers === false) {
            fclose($handle);
            throw new \Exception("Unable to read CSV headers");
        }

        // Read all data rows
        while (($data = fgetcsv($handle)) !== false) {
            $row = [];
            foreach ($headers as $index => $header) {
                $row[trim($header)] = isset($data[$index]) ? trim($data[$index]) : '';
            }
            $rows[] = $row;
        }
        
        fclose($handle);
        
        return [
            'headers' => array_map('trim', $headers),
            'rows' => $rows
        ];
    }

    /**
     * Parse Excel file and return structured data.
     *
     * @param string $filePath
     * @return array
     * @throws \Exception
     */
    public function parseExcel(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        try {
            // Using FastExcel (rap2hpoutre/fast-excel)
            $collection = \Rap2hpoutre\FastExcel\FastExcel::import($filePath);
            $data = $collection->toArray();
            
            if (empty($data)) {
                return [
                    'headers' => [],
                    'rows' => []
                ];
            }

            $headers = array_keys($data[0]);
            
            return [
                'headers' => $headers,
                'rows' => $data
            ];
        } catch (\Exception $e) {
            throw new \Exception("Error parsing Excel file: " . $e->getMessage());
        }
    }

    /**
     * Validate ticket data against existing recordings.
     *
     * @param array $rows CSV/Excel data rows
     * @param array $columnMapping Column name mappings
     * @return array Validation results
     */
    public function validateTicketData(array $rows, array $columnMapping): array
    {
        $results = [];
        
        foreach ($rows as $row) {
            $recordingName = $row[$columnMapping['recording_name'] ?? ''] ?? null;
            $ticketId = $row[$columnMapping['ticket_id'] ?? ''] ?? null;
            
            $result = [
                'recording_name' => $recordingName,
                'ticket_id' => $ticketId,
                'customer_name' => $row[$columnMapping['customer_name'] ?? ''] ?? null,
                'agent_name' => $row[$columnMapping['agent_name'] ?? ''] ?? null,
                'call_intent' => $row[$columnMapping['call_intent'] ?? ''] ?? null,
                'status' => 'error',
                'message' => '',
                'recording_id' => null
            ];
            
            // Validate recording name
            if (empty($recordingName)) {
                $result['message'] = 'Recording name is empty';
                $results[] = $result;
                continue;
            }
            
            // Validate ticket ID
            if (empty($ticketId)) {
                $result['message'] = 'Ticket ID is empty';
                $results[] = $result;
                continue;
            }
            
            // Find recording in database
            $recording = RecordingDetail::where('name', $recordingName)->first();
            
            if (!$recording) {
                $result['status'] = 'not_found';
                $result['message'] = "Recording not found: {$recordingName}";
                $results[] = $result;
                continue;
            }
            
            // Check if already linked
            if ($recording->ticket_id) {
                $result['status'] = 'matched';
                $result['message'] = "Already linked to {$recording->ticket_id} (will be overwritten)";
                $result['recording_id'] = $recording->id;
                $result['existing_ticket'] = $recording->ticket_id;
            } else {
                $result['status'] = 'matched';
                $result['message'] = 'Ready to link';
                $result['recording_id'] = $recording->id;
            }
            
            $results[] = $result;
        }
        
        return $results;
    }

    /**
     * Bulk link recordings from validated CSV data.
     *
     * @param array $validatedRows Validated rows with status='matched'
     * @param array $columnMapping Column name mappings
     * @return array Results with success/failed counts
     */
    public function bulkLinkFromCSV(array $validatedRows, array $columnMapping): array
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        
        DB::transaction(function () use ($validatedRows, $columnMapping, &$successCount, &$failedCount, &$errors) {
            foreach ($validatedRows as $row) {
                try {
                    $recordingName = $row['recording_name'];
                    
                    // Find recording
                    $recording = RecordingDetail::where('name', $recordingName)->first();
                    
                    if (!$recording) {
                        $errors[] = "Recording not found: {$recordingName}";
                        $failedCount++;
                        continue;
                    }
                    
                    // Prepare ticket data
                    $ticketData = [
                        'ticket_id' => $row['ticket_id'] ?? null,
                        'ticket_url' => $row['ticket_url'] ?? null,
                        'customer_name' => $row['customer_name'] ?? null,
                        'agent_name' => $row['agent_name'] ?? null,
                        'call_intent' => $row['call_intent'] ?? null,
                        'call_outcome' => $row['call_outcome'] ?? null,
                    ];
                    
                    // Link recording
                    $this->linkRecordingToTicket($recording, $ticketData);
                    
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Error linking {$row['recording_name']}: {$e->getMessage()}";
                    $failedCount++;
                    Log::error("Bulk link error", [
                        'recording_name' => $row['recording_name'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
        
        return [
            'success' => $successCount,
            'failed' => $failedCount,
            'errors' => $errors
        ];
    }

    /**
     * Get count of unlinked recordings.
     *
     * @return int
     */
    public function getUnlinkedCount(): int
    {
        return RecordingDetail::unlinked()->count();
    }

    /**
     * Get list of unlinked recordings with pagination.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUnlinkedRecordings(int $perPage = 20)
    {
        return RecordingDetail::unlinked()
            ->with('recording')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Unlink recording from ticket.
     *
     * @param RecordingDetail $recording
     * @return RecordingDetail
     */
    public function unlinkRecording(RecordingDetail $recording): RecordingDetail
    {
        $recording->update([
            'ticket_id' => null,
            'ticket_url' => null,
            'customer_name' => null,
            'agent_name' => null,
            'call_intent' => null,
            'call_outcome' => null,
            'display_name' => null,
            'status' => 'unlinked',
            'linked_at' => null,
        ]);

        Log::info("Recording unlinked from ticket", [
            'recording_id' => $recording->id
        ]);

        return $recording->fresh();
    }

    /**
     * Auto-detect column names in CSV headers.
     *
     * @param array $headers
     * @return array Suggested column mapping
     */
    public function autoDetectColumns(array $headers): array
    {
        $mapping = [
            'recording_name' => null,
            'ticket_id' => null,
            'customer_name' => null,
            'agent_name' => null,
            'call_intent' => null,
            'ticket_url' => null,
            'call_outcome' => null,
        ];

        // Define variations for each field
        $variations = [
            'recording_name' => ['recording_name', 'recording_filename', 'filename', 'file', 'recording', 'audio_file', 'file_name'],
            'ticket_id' => ['ticket_id', 'ticket', 'id', 'ticket_number', 'case_id'],
            'customer_name' => ['customer_name', 'customer', 'client_name', 'client', 'name'],
            'agent_name' => ['agent_name', 'agent', 'staff_name', 'staff', 'employee'],
            'call_intent' => ['call_intent', 'intent', 'purpose', 'reason', 'call_purpose', 'type'],
            'ticket_url' => ['ticket_url', 'url', 'link', 'ticket_link'],
            'call_outcome' => ['call_outcome', 'outcome', 'result', 'status', 'resolution'],
        ];

        // Try to match each field
        foreach ($variations as $field => $fieldVariations) {
            foreach ($fieldVariations as $variation) {
                $found = collect($headers)->first(function ($header) use ($variation) {
                    return stripos($header, $variation) !== false;
                });
                
                if ($found) {
                    $mapping[$field] = $found;
                    break;
                }
            }
        }

        return $mapping;
    }
}
