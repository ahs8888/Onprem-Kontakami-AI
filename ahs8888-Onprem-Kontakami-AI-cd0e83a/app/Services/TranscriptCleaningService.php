<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Layer 1: Speech Layer (SST Validation & Cleaning)
 * 
 * Goal: Clean transcriptions, extract timestamps and speaker labels properly
 */
class TranscriptCleaningService
{
    /**
     * Clean and validate transcript from STT
     * 
     * @param string $rawTranscript
     * @return array ['cleaned_text', 'timestamps', 'speakers', 'metadata']
     */
    public function cleanTranscript(string $rawTranscript): array
    {
        $result = [
            'original_text' => $rawTranscript,
            'cleaned_text' => $rawTranscript,
            'timestamps' => [],
            'speakers' => [],
            'filler_words_removed' => 0,
            'noise_tags_removed' => 0,
            'quality_issues' => []
        ];
        
        // Step 1: Extract timestamps
        $result['timestamps'] = $this->extractTimestamps($rawTranscript);
        
        // Step 2: Extract speaker labels
        $result['speakers'] = $this->extractSpeakers($rawTranscript);
        
        // Step 3: Remove filler words
        $cleaned = $this->removeFillerWords($rawTranscript);
        $result['filler_words_removed'] = $cleaned['count'];
        $result['cleaned_text'] = $cleaned['text'];
        
        // Step 4: Remove noise tags
        $cleaned = $this->removeNoiseTags($result['cleaned_text']);
        $result['noise_tags_removed'] = $cleaned['count'];
        $result['cleaned_text'] = $cleaned['text'];
        
        // Step 5: Quality validation
        $result['quality_issues'] = $this->validateQuality($result);
        
        // Step 6: Calculate confidence score
        $result['confidence_score'] = $this->calculateConfidenceScore($result);
        
        return $result;
    }
    
    /**
     * Detect timestamps using regex pattern: \b\d{1,2}:\d{2}:\d{2}\b
     * Captures times like 00:01:12
     */
    private function extractTimestamps(string $text): array
    {
        $pattern = '/\b\d{1,2}:\d{2}:\d{2}\b/';
        preg_match_all($pattern, $text, $matches);
        
        $timestamps = [];
        foreach ($matches[0] as $timestamp) {
            $timestamps[] = [
                'time' => $timestamp,
                'seconds' => $this->timestampToSeconds($timestamp)
            ];
        }
        
        return $timestamps;
    }
    
    /**
     * Convert timestamp to seconds
     */
    private function timestampToSeconds(string $timestamp): int
    {
        $parts = explode(':', $timestamp);
        $hours = (int)($parts[0] ?? 0);
        $minutes = (int)($parts[1] ?? 0);
        $seconds = (int)($parts[2] ?? 0);
        
        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }
    
    /**
     * Identify speaker labels using pattern: (?i)\b(agent|customer)\b
     * Detects "agent" and "customer" (case insensitive)
     */
    private function extractSpeakers(string $text): array
    {
        $pattern = '/(?i)\b(agent|customer)\b/';
        preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
        
        $speakers = [];
        foreach ($matches[0] as $match) {
            $speakers[] = [
                'label' => strtolower($match[0]),
                'position' => $match[1]
            ];
        }
        
        // Count occurrences
        $speakerCounts = array_count_values(array_column($speakers, 'label'));
        
        return [
            'instances' => $speakers,
            'counts' => $speakerCounts,
            'total_turns' => count($speakers)
        ];
    }
    
    /**
     * Remove filler words using pattern: \b(uh|um)\b
     */
    private function removeFillerWords(string $text): array
    {
        $pattern = '/\b(uh|um)\b/i';
        $cleanedText = preg_replace($pattern, '', $text);
        
        // Count removed instances
        preg_match_all($pattern, $text, $matches);
        $count = count($matches[0]);
        
        // Clean up extra spaces
        $cleanedText = preg_replace('/\s+/', ' ', $cleanedText);
        $cleanedText = trim($cleanedText);
        
        return [
            'text' => $cleanedText,
            'count' => $count
        ];
    }
    
    /**
     * Remove noise tags using pattern: \[(noise|silence)\]
     */
    private function removeNoiseTags(string $text): array
    {
        $pattern = '/\[(noise|silence)\]/i';
        $cleanedText = preg_replace($pattern, '', $text);
        
        // Count removed instances
        preg_match_all($pattern, $text, $matches);
        $count = count($matches[0]);
        
        // Clean up extra spaces
        $cleanedText = preg_replace('/\s+/', ' ', $cleanedText);
        $cleanedText = trim($cleanedText);
        
        return [
            'text' => $cleanedText,
            'count' => $count
        ];
    }
    
    /**
     * Validate transcript quality
     */
    private function validateQuality(array $result): array
    {
        $issues = [];
        
        // Check if transcript is too short
        if (strlen($result['cleaned_text']) < 50) {
            $issues[] = 'Transcript too short (< 50 characters)';
        }
        
        // Check if no speakers detected
        if (empty($result['speakers']['instances'])) {
            $issues[] = 'No speaker labels detected';
        }
        
        // Check if too many filler words (> 20% of words)
        $wordCount = str_word_count($result['original_text']);
        if ($wordCount > 0) {
            $fillerPercentage = ($result['filler_words_removed'] / $wordCount) * 100;
            if ($fillerPercentage > 20) {
                $issues[] = sprintf('High filler word density (%.1f%%)', $fillerPercentage);
            }
        }
        
        // Check if too many noise tags
        if ($result['noise_tags_removed'] > 5) {
            $issues[] = sprintf('High noise level (%d noise tags)', $result['noise_tags_removed']);
        }
        
        return $issues;
    }
    
    /**
     * Calculate overall confidence score (0-100)
     */
    private function calculateConfidenceScore(array $result): float
    {
        $score = 100.0;
        
        // Deduct for quality issues
        foreach ($result['quality_issues'] as $issue) {
            if (strpos($issue, 'too short') !== false) {
                $score -= 30;
            } elseif (strpos($issue, 'No speaker') !== false) {
                $score -= 20;
            } elseif (strpos($issue, 'High filler') !== false) {
                $score -= 15;
            } elseif (strpos($issue, 'High noise') !== false) {
                $score -= 10;
            }
        }
        
        // Bonus for having timestamps
        if (count($result['timestamps']) > 0) {
            $score += 5;
        }
        
        // Bonus for balanced speaker turns
        if (!empty($result['speakers']['counts'])) {
            $turnCounts = array_values($result['speakers']['counts']);
            if (count($turnCounts) >= 2) {
                $ratio = min($turnCounts) / max($turnCounts);
                if ($ratio > 0.4) { // Reasonably balanced conversation
                    $score += 5;
                }
            }
        }
        
        return max(0, min(100, $score));
    }
    
    /**
     * Format cleaned transcript with speaker labels and timestamps
     */
    public function formatTranscript(array $cleanedData): string
    {
        $formatted = "=== CLEANED TRANSCRIPT ===\n\n";
        $formatted .= $cleanedData['cleaned_text'];
        $formatted .= "\n\n=== METADATA ===\n";
        $formatted .= "Confidence Score: {$cleanedData['confidence_score']}%\n";
        $formatted .= "Timestamps Found: " . count($cleanedData['timestamps']) . "\n";
        $formatted .= "Speaker Turns: " . ($cleanedData['speakers']['total_turns'] ?? 0) . "\n";
        $formatted .= "Filler Words Removed: {$cleanedData['filler_words_removed']}\n";
        $formatted .= "Noise Tags Removed: {$cleanedData['noise_tags_removed']}\n";
        
        if (!empty($cleanedData['quality_issues'])) {
            $formatted .= "\nQuality Issues:\n";
            foreach ($cleanedData['quality_issues'] as $issue) {
                $formatted .= "  - $issue\n";
            }
        }
        
        return $formatted;
    }
}
