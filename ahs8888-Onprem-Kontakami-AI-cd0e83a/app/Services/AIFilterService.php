<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * AI Micro-Decisions Service
 * 
 * Makes autonomous decisions about:
 * - Quality (confidence score)
 * - Privacy (PII detection)
 * - Relevance (duration, content)
 * - Upload eligibility
 */
class AIFilterService
{
    private TranscriptCleaningService $cleaningService;
    
    public function __construct(TranscriptCleaningService $cleaningService)
    {
        $this->cleaningService = $cleaningService;
    }
    
    /**
     * Evaluate recording and make upload decision
     * 
     * @param array $recordingData
     * @return object Decision object with shouldUpload, reason, metadata
     */
    public function evaluateRecording(array $recordingData): object
    {
        $decision = (object)[
            'shouldUpload' => false,
            'reason' => '',
            'confidence_score' => 0,
            'quality_score' => 0,
            'contains_pii' => false,
            'pii_detected' => [],
            'cleaned_transcript' => '',
            'metadata' => [],
            'recommended_action' => 'review', // review, re-record, upload, discard
            'filters_applied' => []
        ];
        
        // Get thresholds from config
        $thresholds = $this->getThresholds();
        
        // Step 1: Clean transcript (Layer 1)
        $cleanedData = $this->cleaningService->cleanTranscript($recordingData['transcript'] ?? '');
        $decision->confidence_score = $cleanedData['confidence_score'];
        $decision->cleaned_transcript = $cleanedData['cleaned_text'];
        $decision->metadata = $cleanedData;
        
        // Step 2: Check duration
        $duration = $recordingData['duration'] ?? 0;
        if ($duration < $thresholds['min_duration']) {
            $decision->reason = "Duration too short ({$duration}s < {$thresholds['min_duration']}s)";
            $decision->recommended_action = 'discard';
            $decision->filters_applied[] = 'duration_check';
            return $decision;
        }
        
        if ($duration > $thresholds['max_duration']) {
            $decision->reason = "Duration too long ({$duration}s > {$thresholds['max_duration']}s)";
            $decision->recommended_action = 'review';
            $decision->filters_applied[] = 'duration_check';
            return $decision;
        }
        
        // Step 3: Check confidence score
        if ($decision->confidence_score < $thresholds['min_confidence']) {
            $decision->reason = "Low confidence score ({$decision->confidence_score}% < {$thresholds['min_confidence']}%)";
            $decision->recommended_action = 're-record';
            $decision->filters_applied[] = 'confidence_check';
            return $decision;
        }
        
        // Step 4: Detect PII
        $piiResult = $this->detectPII($decision->cleaned_transcript);
        $decision->contains_pii = $piiResult['has_pii'];
        $decision->pii_detected = $piiResult['detected'];
        
        if ($decision->contains_pii && $thresholds['block_pii']) {
            $decision->reason = "PII detected: " . implode(', ', array_keys($piiResult['detected']));
            $decision->recommended_action = 'review';
            $decision->filters_applied[] = 'pii_check';
            
            // If redact_pii is enabled, we can still upload with redaction
            if ($thresholds['redact_pii']) {
                $decision->shouldUpload = true;
                $decision->cleaned_transcript = $piiResult['redacted_text'];
                $decision->reason = "PII redacted and safe to upload";
                $decision->recommended_action = 'upload';
            }
            return $decision;
        }
        
        // Step 5: Quality score calculation
        $decision->quality_score = $this->calculateQualityScore([
            'confidence' => $decision->confidence_score,
            'duration' => $duration,
            'has_speakers' => !empty($cleanedData['speakers']['instances']),
            'has_timestamps' => !empty($cleanedData['timestamps']),
            'quality_issues' => $cleanedData['quality_issues']
        ]);
        
        if ($decision->quality_score < $thresholds['min_quality_score']) {
            $decision->reason = "Quality score too low ({$decision->quality_score}% < {$thresholds['min_quality_score']}%)";
            $decision->recommended_action = 're-record';
            $decision->filters_applied[] = 'quality_check';
            return $decision;
        }
        
        // All checks passed!
        $decision->shouldUpload = true;
        $decision->reason = "All quality checks passed";
        $decision->recommended_action = 'upload';
        $decision->filters_applied = ['duration_check', 'confidence_check', 'pii_check', 'quality_check'];
        
        return $decision;
    }
    
    /**
     * Detect PII using regex patterns + AI
     * 
     * Detects:
     * - Credit card numbers
     * - SSN
     * - Email addresses
     * - Phone numbers
     * - Account numbers
     */
    private function detectPII(string $text): array
    {
        $detected = [];
        $redactedText = $text;
        
        // Credit card numbers (16 digits with optional spaces/dashes)
        $ccPattern = '/\b(?:\d{4}[\s\-]?){3}\d{4}\b/';
        if (preg_match($ccPattern, $text, $matches)) {
            $detected['credit_card'] = $matches;
            $redactedText = preg_replace($ccPattern, '[REDACTED-CC]', $redactedText);
        }
        
        // SSN (XXX-XX-XXXX)
        $ssnPattern = '/\b\d{3}-\d{2}-\d{4}\b/';
        if (preg_match($ssnPattern, $text, $matches)) {
            $detected['ssn'] = $matches;
            $redactedText = preg_replace($ssnPattern, '[REDACTED-SSN]', $redactedText);
        }
        
        // Email addresses
        $emailPattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';
        if (preg_match($emailPattern, $text, $matches)) {
            $detected['email'] = $matches;
            $redactedText = preg_replace($emailPattern, '[REDACTED-EMAIL]', $redactedText);
        }
        
        // Phone numbers (various formats)
        $phonePattern = '/\b(?:\+?1[-.\s]?)?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})\b/';
        if (preg_match($phonePattern, $text, $matches)) {
            $detected['phone'] = $matches;
            $redactedText = preg_replace($phonePattern, '[REDACTED-PHONE]', $redactedText);
        }
        
        // Account numbers (8-12 digits)
        $accountPattern = '/\b(?:account|acct)[\s#:]*(\d{8,12})\b/i';
        if (preg_match($accountPattern, $text, $matches)) {
            $detected['account_number'] = $matches;
            $redactedText = preg_replace($accountPattern, 'account [REDACTED-ACCOUNT]', $redactedText);
        }
        
        return [
            'has_pii' => !empty($detected),
            'detected' => $detected,
            'redacted_text' => $redactedText
        ];
    }
    
    /**
     * Calculate overall quality score (0-100)
     */
    private function calculateQualityScore(array $factors): float
    {
        $score = 0;
        
        // Confidence contributes 40%
        $score += ($factors['confidence'] * 0.4);
        
        // Duration appropriateness contributes 20%
        $duration = $factors['duration'];
        if ($duration >= 30 && $duration <= 1800) { // 30s to 30min is good
            $score += 20;
        } elseif ($duration > 1800 && $duration <= 3600) { // Up to 1 hour is okay
            $score += 15;
        } else {
            $score += 10;
        }
        
        // Speaker detection contributes 20%
        if ($factors['has_speakers']) {
            $score += 20;
        }
        
        // Timestamp presence contributes 10%
        if ($factors['has_timestamps']) {
            $score += 10;
        }
        
        // Deduct for quality issues (10% each)
        $issueCount = count($factors['quality_issues'] ?? []);
        $score -= ($issueCount * 10);
        
        return max(0, min(100, $score));
    }
    
    /**
     * Get thresholds from config (dynamic, user-configurable)
     */
    private function getThresholds(): array
    {
        return [
            'min_confidence' => (float) config('ai_filter.min_confidence', 70.0),
            'min_quality_score' => (float) config('ai_filter.min_quality_score', 60.0),
            'min_duration' => (int) config('ai_filter.min_duration', 30), // seconds
            'max_duration' => (int) config('ai_filter.max_duration', 3600), // 1 hour
            'block_pii' => (bool) config('ai_filter.block_pii', true),
            'redact_pii' => (bool) config('ai_filter.redact_pii', true),
        ];
    }
    
    /**
     * Check if recording should be retried
     */
    public function shouldRetry(object $decision, int $attemptCount): bool
    {
        $maxRetries = (int) config('ai_filter.max_retries', 1);
        
        if ($attemptCount >= $maxRetries) {
            return false;
        }
        
        // Only retry if it's a re-record recommendation
        return $decision->recommended_action === 're-record';
    }
}
