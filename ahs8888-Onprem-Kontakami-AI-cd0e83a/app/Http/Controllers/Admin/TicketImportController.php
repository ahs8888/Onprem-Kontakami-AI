<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketLinkingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketImportController extends Controller
{
    protected TicketLinkingService $ticketService;
    
    public function __construct(TicketLinkingService $ticketService)
    {
        $this->ticketService = $ticketService;
    }
    
    /**
     * Display the ticket import page
     */
    public function index()
    {
        return Inertia::render('TicketImport/Index');
    }
    
    /**
     * Parse uploaded CSV/Excel file
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240' // 10MB max
        ]);
        
        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $filePath = $file->getRealPath();
            
            // Parse based on file type
            if ($extension === 'csv') {
                $result = $this->ticketService->parseCSV($filePath);
            } else {
                $result = $this->ticketService->parseExcel($filePath);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error parsing file: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validate column mapping against database
     */
    public function validateMapping(Request $request)
    {
        $request->validate([
            'column_mapping' => 'required|array',
            'column_mapping.recording_name' => 'required|string',
            'column_mapping.ticket_id' => 'required|string',
            'csv_data' => 'required|array'
        ]);
        
        try {
            $columnMapping = $request->input('column_mapping');
            $csvData = $request->input('csv_data');
            
            // Validate the data
            $results = $this->ticketService->validateTicketData($csvData, $columnMapping);
            
            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Perform bulk linking of recordings to tickets
     */
    public function bulkLink(Request $request)
    {
        $request->validate([
            'records' => 'required|array',
            'column_mapping' => 'required|array'
        ]);
        
        try {
            $records = $request->input('records');
            
            // Perform bulk linking
            $result = $this->ticketService->bulkLinkFromCSV($records);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
