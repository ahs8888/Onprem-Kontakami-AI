<?php

namespace App\Services;

use App\Models\Data\RecordingDetail;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TicketLinkingService
{
    /**
     * Generate a human-readable display name for a recording
     * Format: CustomerName_TicketID_Date or TicketID_Date or original filename
     */
    public function generateDisplayName(RecordingDetail $recording, array $ticketData = []): string
    {
        $parts = [];
        
        // Add customer name if available
        if (!empty($ticketData['customer_name'])) {
            $parts[] = $this->sanitizeForFilename($ticketData['customer_name']);
        }
        
        // Add ticket ID if available
        if (!empty($ticketData['ticket_id'])) {
            $parts[] = $this->sanitizeForFilename($ticketData['ticket_id']);
        }
        
        // Add date if available from recording
        if ($recording->created_at) {
            $parts[] = $recording->created_at->format('Ymd');
        }
        
        // If we have parts, join them with underscores, otherwise use original name
        return !empty($parts) ? implode('_', $parts) : $recording->name;
    }
    
    /**
     * Sanitize a string for use in filenames
     */
    private function sanitizeForFilename(string $text): string
    {
        // Remove special characters, keep alphanumeric, spaces, hyphens, underscores
        $text = preg_replace('/[^A-Za-z0-9\s\-_]/', '', $text);
        // Replace spaces with underscores
        $text = str_replace(' ', '_', $text);
        // Remove multiple consecutive underscores
        $text = preg_replace('/_+/', '_', $text);
        // Trim underscores from start and end
        return trim($text, '_');
    }
    
    /**
     * Link a single recording to ticket information
     */
    public function linkRecordingToTicket(RecordingDetail $recording, array $ticketData): bool
    {
        try {
            // Generate display name
            $displayName = $this->generateDisplayName($recording, $ticketData);
            
            // Update recording with ticket information
            $recording->update([
                'display_name' => $displayName,
                'ticket_id' => $ticketData['ticket_id'] ?? null,
                'ticket_url' => $ticketData['ticket_url'] ?? null,
                'customer_name' => $ticketData['customer_name'] ?? null,
                'agent_name' => $ticketData['agent_name'] ?? null,
                'call_intent' => $ticketData['call_intent'] ?? null,
                'call_outcome' => $ticketData['call_outcome'] ?? null,
                'status' => 'linked',
                'linked_at' => now(),
            ]);
            
            Log::info("Recording linked to ticket", [
                'recording_id' => $recording->id,
                'ticket_id' => $ticketData['ticket_id'] ?? 'N/A',
                'display_name' => $displayName
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to link recording to ticket", [
                'recording_id' => $recording->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Parse CSV file and return headers and rows
     */
    public function parseCSV(string $filePath): array
    {
        $headers = [];
        $rows = [];
        
        try {
            if (($handle = fopen($filePath, 'r')) !== false) {
                // First row is headers
                $headers = fgetcsv($handle);
                
                // Read data rows (limit to 1000 for preview)
                $rowCount = 0;
                while (($data = fgetcsv($handle)) !== false && $rowCount < 1000) {
                    $row = [];
                    foreach ($headers as $index => $header) {
                        $row[$header] = $data[$index] ?? '';
                    }
                    $rows[] = $row;
                    $rowCount++;
                }
                fclose($handle);
            }
            
            return [
                'success' => true,
                'headers' => $headers,
                'rows' => $rows,
                'total_rows' => count($rows)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to parse CSV: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse Excel file and return headers and rows
     */
    public function parseExcel(string $filePath): array
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            if (empty($data)) {
                return [
                    'success' => false,
                    'message' => 'Excel file is empty'
                ];
            }
            
            // First row is headers
            $headers = array_shift($data);
            
            // Convert rows to associative arrays
            $rows = [];
            $rowCount = 0;
            foreach ($data as $rowData) {
                if ($rowCount >= 1000) break; // Limit preview
                
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = $rowData[$index] ?? '';
                }
                $rows[] = $row;
                $rowCount++;
            }
            
            return [
                'success' => true,
                'headers' => $headers,
                'rows' => $rows,
                'total_rows' => count($rows)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to parse Excel: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate ticket data from CSV against database recordings
     */
    public function validateTicketData(array $csvData, array $columnMapping): array
    {
        $results = [];
        
        foreach ($csvData as $row) {
            $recordingName = $row[$columnMapping['recording_name']] ?? '';
            $ticketId = $row[$columnMapping['ticket_id']] ?? '';
            
            // Check if required fields are present
            if (empty($recordingName) || empty($ticketId)) {
                $results[] = [
                    'recording_name' => $recordingName ?: 'N/A',
                    'ticket_id' => $ticketId ?: 'N/A',
                    'customer_name' => $row[$columnMapping['customer_name']] ?? '',
                    'status' => 'failed',
                    'message' => 'Missing required fields (recording name or ticket ID)'
                ];
                continue;
            }
            
            // Try to find recording by exact name match
            $recording = RecordingDetail::where('name', $recordingName)->first();
            
            if (!$recording) {
                // Try fuzzy match (without extension)
                $nameWithoutExt = pathinfo($recordingName, PATHINFO_FILENAME);
                $recording = RecordingDetail::where('name', 'like', $nameWithoutExt . '%')->first();
            }
            
            if ($recording) {
                $results[] = [
                    'recording_name' => $recordingName,
                    'ticket_id' => $ticketId,
                    'customer_name' => $row[$columnMapping['customer_name']] ?? '',
                    'agent_name' => $row[$columnMapping['agent_name']] ?? '',
                    'call_intent' => $row[$columnMapping['call_intent']] ?? '',
                    'call_outcome' => $row[$columnMapping['call_outcome']] ?? '',
                    'ticket_url' => $row[$columnMapping['ticket_url']] ?? '',
                    'status' => 'matched',
                    'message' => 'Recording found in database',
                    'recording_id' => $recording->id
                ];
            } else {
                $results[] = [
                    'recording_name' => $recordingName,
                    'ticket_id' => $ticketId,
                    'customer_name' => $row[$columnMapping['customer_name']] ?? '',
                    'status' => 'unmatched',
                    'message' => 'Recording not found in database'
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Bulk link recordings from validated CSV data
     */
    public function bulkLinkFromCSV(array $validatedRecords): array
    {
        $linkedCount = 0;
        $failedCount = 0;
        $errors = [];
        
        foreach ($validatedRecords as $record) {
            if ($record['status'] !== 'matched' || empty($record['recording_id'])) {
                continue;
            }
            
            $recording = RecordingDetail::find($record['recording_id']);
            if (!$recording) {
                $failedCount++;
                $errors[] = "Recording ID {$record['recording_id']} not found";
                continue;
            }
            
            $ticketData = [
                'ticket_id' => $record['ticket_id'],
                'customer_name' => $record['customer_name'] ?? null,
                'agent_name' => $record['agent_name'] ?? null,
                'call_intent' => $record['call_intent'] ?? null,
                'call_outcome' => $record['call_outcome'] ?? null,
                'ticket_url' => $record['ticket_url'] ?? null,
            ];
            
            if ($this->linkRecordingToTicket($recording, $ticketData)) {
                $linkedCount++;
            } else {
                $failedCount++;
                $errors[] = "Failed to link recording: {$record['recording_name']}";
            }
        }
        
        return [
            'success' => true,
            'linked_count' => $linkedCount,
            'failed_count' => $failedCount,
            'errors' => $errors
        ];
    }
    
    /**
     * Get count of unlinked recordings that require tickets
     */
    public function getUnlinkedCount(): int
    {
        return RecordingDetail::where('requires_ticket', true)
            ->where('status', 'unlinked')
            ->count();
    }
    
    /**
     * Get list of unlinked recordings
     */
    public function getUnlinkedRecordings(int $limit = 100)
    {
        return RecordingDetail::where('requires_ticket', true)
            ->where('status', 'unlinked')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
