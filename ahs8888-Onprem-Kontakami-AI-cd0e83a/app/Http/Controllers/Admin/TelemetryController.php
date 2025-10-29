<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TelemetryService;
use Illuminate\Http\Request;

class TelemetryController extends Controller
{
    protected TelemetryService $telemetryService;
    
    public function __construct(TelemetryService $telemetryService)
    {
        $this->telemetryService = $telemetryService;
    }
    
    /**
     * Get local system status
     * Endpoint: /status/local
     */
    public function localStatus()
    {
        $status = $this->telemetryService->getSystemStatus();
        
        // Log telemetry for historical tracking
        $this->telemetryService->logTelemetry($status);
        
        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }
    
    /**
     * Get health check (simplified status)
     */
    public function healthCheck()
    {
        $status = $this->telemetryService->getSystemStatus();
        
        // Determine overall health
        $issues = [];
        
        if ($status['queue']['queue_health'] === 'congested') {
            $issues[] = 'Queue congested';
        }
        
        if ($status['storage']['disk_health'] === 'critical') {
            $issues[] = 'Disk space critical';
        }
        
        if ($status['queue']['failed_jobs'] > 10) {
            $issues[] = 'High failed job count';
        }
        
        $isHealthy = empty($issues);
        
        return response()->json([
            'healthy' => $isHealthy,
            'status' => $isHealthy ? 'OK' : 'DEGRADED',
            'issues' => $issues,
            'timestamp' => now()->toIso8601String()
        ], $isHealthy ? 200 : 503);
    }
}
