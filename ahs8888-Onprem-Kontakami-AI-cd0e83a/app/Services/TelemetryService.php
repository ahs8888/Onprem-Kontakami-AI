<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Data\Recording;
use App\Models\Data\RecordingDetail;

/**
 * Telemetry Service for Local System Monitoring
 * 
 * Tracks:
 * - Queue size
 * - Bandwidth usage
 * - Upload status
 * - Processing metrics
 */
class TelemetryService
{
    /**
     * Get current system status
     */
    public function getSystemStatus(): array
    {
        return [
            'timestamp' => now()->toIso8601String(),
            'queue' => $this->getQueueMetrics(),
            'bandwidth' => $this->getBandwidthMetrics(),
            'recordings' => $this->getRecordingMetrics(),
            'storage' => $this->getStorageMetrics(),
            'processing' => $this->getProcessingMetrics(),
            'quality' => $this->getQualityMetrics(),
        ];
    }
    
    /**
     * Get queue metrics
     */
    private function getQueueMetrics(): array
    {
        // Count pending jobs
        $pendingJobs = DB::table('jobs')->count();
        
        // Count failed jobs
        $failedJobs = DB::table('failed_jobs')->count();
        
        // Get recordings in queue
        $recordingsInProgress = Recording::where('status', 'Progress')->count();
        $recordingsPending = RecordingDetail::where('is_transcript', 0)->count();
        
        return [
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'recordings_in_progress' => $recordingsInProgress,
            'recordings_pending_stt' => $recordingsPending,
            'queue_health' => $pendingJobs < 100 ? 'healthy' : ($pendingJobs < 500 ? 'moderate' : 'congested')
        ];
    }
    
    /**
     * Get bandwidth usage metrics
     */
    private function getBandwidthMetrics(): array
    {
        // Calculate bandwidth from last hour
        $oneHourAgo = now()->subHour();
        
        $recentUploads = RecordingDetail::where('transfer_cloud', 1)
            ->where('updated_at', '>=', $oneHourAgo)
            ->count();
        
        // Estimate average file size (rough estimate)
        $avgFileSize = 2.5; // MB
        $bandwidthUsed = $recentUploads * $avgFileSize;
        
        // Get today's total
        $todayUploads = RecordingDetail::where('transfer_cloud', 1)
            ->whereDate('updated_at', now()->toDateString())
            ->count();
        
        $todayBandwidth = $todayUploads * $avgFileSize;
        
        return [
            'last_hour' => [
                'uploads' => $recentUploads,
                'bandwidth_mb' => round($bandwidthUsed, 2),
                'rate_mbps' => round($bandwidthUsed / 3600, 4) // MB per second
            ],
            'today' => [
                'uploads' => $todayUploads,
                'bandwidth_mb' => round($todayBandwidth, 2),
                'bandwidth_gb' => round($todayBandwidth / 1024, 2)
            ],
            'estimated_monthly_gb' => round(($todayBandwidth / 1024) * 30, 2)
        ];
    }
    
    /**
     * Get recording metrics
     */
    private function getRecordingMetrics(): array
    {
        $total = RecordingDetail::count();
        $processed = RecordingDetail::where('is_transcript', 1)->count();
        $uploaded = RecordingDetail::where('transfer_cloud', 1)->count();
        $failed = RecordingDetail::where('status', 'Failed')->count();
        
        // Quality metrics
        $highQuality = RecordingDetail::where('confidence_score', '>=', 80)->count();
        $mediumQuality = RecordingDetail::whereBetween('confidence_score', [60, 79])->count();
        $lowQuality = RecordingDetail::where('confidence_score', '<', 60)->count();
        
        // PII detection
        $withPII = RecordingDetail::where('pii_detected', true)->count();
        
        return [
            'total' => $total,
            'processed' => $processed,
            'uploaded' => $uploaded,
            'failed' => $failed,
            'pending' => $total - $processed,
            'success_rate' => $total > 0 ? round(($processed - $failed) / $total * 100, 2) : 0,
            'upload_rate' => $processed > 0 ? round($uploaded / $processed * 100, 2) : 0,
            'quality_distribution' => [
                'high' => $highQuality,
                'medium' => $mediumQuality,
                'low' => $lowQuality
            ],
            'pii_detected' => $withPII
        ];
    }
    
    /**
     * Get storage metrics
     */
    private function getStorageMetrics(): array
    {
        $storagePath = storage_path('app/public/uploads');
        
        // Get total size
        $totalSize = 0;
        if (is_dir($storagePath)) {
            $totalSize = $this->getDirectorySize($storagePath);
        }
        
        // Get disk space
        $diskFree = disk_free_space($storagePath) ?? 0;
        $diskTotal = disk_total_space($storagePath) ?? 1;
        $diskUsed = $diskTotal - $diskFree;
        $diskUsagePercent = round(($diskUsed / $diskTotal) * 100, 2);
        
        return [
            'recordings_size_mb' => round($totalSize / (1024 * 1024), 2),
            'recordings_size_gb' => round($totalSize / (1024 * 1024 * 1024), 2),
            'disk_free_gb' => round($diskFree / (1024 * 1024 * 1024), 2),
            'disk_total_gb' => round($diskTotal / (1024 * 1024 * 1024), 2),
            'disk_usage_percent' => $diskUsagePercent,
            'disk_health' => $diskUsagePercent < 80 ? 'healthy' : ($diskUsagePercent < 90 ? 'warning' : 'critical')
        ];
    }
    
    /**
     * Get processing metrics
     */
    private function getProcessingMetrics(): array
    {
        // Get average processing time (last 100 recordings)
        $recentRecordings = RecordingDetail::where('is_transcript', 1)
            ->orderBy('updated_at', 'desc')
            ->limit(100)
            ->get(['created_at', 'updated_at']);
        
        $totalProcessingTime = 0;
        $count = 0;
        
        foreach ($recentRecordings as $recording) {
            if ($recording->created_at && $recording->updated_at) {
                $processingTime = $recording->created_at->diffInSeconds($recording->updated_at);
                $totalProcessingTime += $processingTime;
                $count++;
            }
        }
        
        $avgProcessingTime = $count > 0 ? round($totalProcessingTime / $count, 2) : 0;
        
        // Get recordings processed today
        $processedToday = RecordingDetail::where('is_transcript', 1)
            ->whereDate('updated_at', now()->toDateString())
            ->count();
        
        return [
            'avg_processing_time_seconds' => $avgProcessingTime,
            'processed_today' => $processedToday,
            'estimated_daily_capacity' => $avgProcessingTime > 0 ? round(86400 / $avgProcessingTime) : 0,
            'processing_health' => $avgProcessingTime < 30 ? 'fast' : ($avgProcessingTime < 60 ? 'normal' : 'slow')
        ];
    }
    
    /**
     * Get quality metrics
     */
    private function getQualityMetrics(): array
    {
        $avgConfidence = RecordingDetail::where('is_transcript', 1)
            ->avg('confidence_score') ?? 0;
        
        $avgQuality = RecordingDetail::where('is_transcript', 1)
            ->avg('quality_score') ?? 0;
        
        $autoFilteredOut = RecordingDetail::where('upload_decision', 'rejected')->count();
        $autoApproved = RecordingDetail::where('upload_decision', 'approved')->count();
        
        return [
            'avg_confidence_score' => round($avgConfidence, 2),
            'avg_quality_score' => round($avgQuality, 2),
            'auto_filtered_out' => $autoFilteredOut,
            'auto_approved' => $autoApproved,
            'filter_rate' => ($autoFilteredOut + $autoApproved) > 0 
                ? round($autoFilteredOut / ($autoFilteredOut + $autoApproved) * 100, 2) 
                : 0
        ];
    }
    
    /**
     * Calculate directory size recursively
     */
    private function getDirectorySize(string $path): int
    {
        $size = 0;
        
        try {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            Log::warning("Could not calculate directory size", ['path' => $path, 'error' => $e->getMessage()]);
        }
        
        return $size;
    }
    
    /**
     * Log telemetry to database for historical tracking
     */
    public function logTelemetry(array $data): void
    {
        DB::table('telemetry_logs')->insert([
            'timestamp' => now(),
            'queue_pending' => $data['queue']['pending_jobs'] ?? 0,
            'recordings_processed' => $data['recordings']['processed'] ?? 0,
            'recordings_uploaded' => $data['recordings']['uploaded'] ?? 0,
            'bandwidth_mb' => $data['bandwidth']['today']['bandwidth_mb'] ?? 0,
            'avg_confidence' => $data['quality']['avg_confidence_score'] ?? 0,
            'disk_usage_percent' => $data['storage']['disk_usage_percent'] ?? 0,
            'data' => json_encode($data),
            'created_at' => now()
        ]);
    }
}
