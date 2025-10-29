<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketLinkingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Ticket Import Controller
 * 
 * Handles CSV/Excel bulk import for ticket linking operations
 */
class TicketImportController extends Controller
{
    protected TicketLinkingService $ticketService;

    public function __construct(TicketLinkingService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Show ticket import page
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('TicketImport/Index');
    }

    /**
     * Parse uploaded CSV/Excel file and return headers + preview
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240' // 10MB max
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        try {
            if (in_array($extension, ['csv', 'txt'])) {
                $data = $this->ticketService->parseCSV($file->getRealPath());
            } else {
                $data = $this->ticketService->parseExcel($file->getRealPath());
            }

            return response()->json([
                'success' => true,
                'headers' => $data['headers'] ?? [],
                'rows' => $data['rows'] ?? [],
                'row_count' => count($data['rows'] ?? [])
            ]);
        } catch (\Exception $e) {
            \Log::error('CSV Parse Error', [
                'message' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to parse file: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Validate column mapping and return match results
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateMapping(Request $request)
    {
        $request->validate([
            'column_mapping' => 'required|array',
            'column_mapping.recording_name' => 'required|string',
            'column_mapping.ticket_id' => 'required|string',
            'csv_data' => 'required|array'
        ]);

        $columnMapping = $request->input('column_mapping');
        $csvData = $request->input('csv_data');

        try {
            $results = $this->ticketService->validateTicketData($csvData, $columnMapping);

            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            \Log::error('Validation Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Perform bulk ticket linking
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkLink(Request $request)
    {
        $request->validate([
            'records' => 'required|array',
            'column_mapping' => 'required|array'
        ]);

        $validatedRows = $request->input('records');
        $columnMapping = $request->input('column_mapping');

        try {
            $result = $this->ticketService->bulkLinkFromCSV($validatedRows, $columnMapping);

            \Log::info('Bulk Link Complete', [
                'success_count' => $result['success'],
                'failed_count' => $result['failed']
            ]);

            return response()->json([
                'success' => true,
                'linked_count' => $result['success'],
                'failed_count' => $result['failed'],
                'errors' => $result['errors'],
                'message' => "{$result['success']} recordings linked successfully"
            ]);
        } catch (\Exception $e) {
            \Log::error('Bulk Link Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Download not-found recordings report as CSV
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadNotFoundReport(Request $request)
    {
        $notFoundData = $request->input('not_found', []);

        $filename = 'not-found-recordings-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($notFoundData) {
            $file = fopen('php://output', 'w');

            // Write headers
            fputcsv($file, ['Recording Name', 'Ticket ID', 'Status', 'Message']);

            // Write data
            foreach ($notFoundData as $row) {
                fputcsv($file, [
                    $row['recording_name'] ?? '',
                    $row['ticket_id'] ?? '',
                    $row['status'] ?? '',
                    $row['message'] ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
