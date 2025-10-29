<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | AI Filter Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the autonomous filtering and quality checks
    | before recordings are uploaded to cloud. Adjust thresholds based on
    | your quality requirements.
    |
    */
    
    // Minimum confidence score (0-100) for transcript quality
    // Recordings below this threshold will be flagged for re-recording
    'min_confidence' => env('AI_FILTER_MIN_CONFIDENCE', 70.0),
    
    // Minimum overall quality score (0-100)
    // Combines confidence, duration, speaker detection, etc.
    'min_quality_score' => env('AI_FILTER_MIN_QUALITY_SCORE', 60.0),
    
    // Minimum recording duration in seconds
    // Recordings shorter than this are considered too brief
    'min_duration' => env('AI_FILTER_MIN_DURATION', 30),
    
    // Maximum recording duration in seconds
    // Recordings longer than this may be flagged for review
    'max_duration' => env('AI_FILTER_MAX_DURATION', 3600), // 1 hour
    
    // Block recordings containing PII (Personally Identifiable Information)
    // If true, recordings with detected PII won't be uploaded (unless redacted)
    'block_pii' => env('AI_FILTER_BLOCK_PII', true),
    
    // Automatically redact PII before upload
    // If true, PII will be replaced with [REDACTED] tags
    'redact_pii' => env('AI_FILTER_REDACT_PII', true),
    
    // Maximum number of retry attempts for failed recordings
    'max_retries' => env('AI_FILTER_MAX_RETRIES', 1),
    
    // Enable encryption for cloud uploads
    'enable_encryption' => env('AI_FILTER_ENABLE_ENCRYPTION', true),
    
    // Encryption algorithm (aes-256-cbc, aes-128-cbc)
    'encryption_algorithm' => env('AI_FILTER_ENCRYPTION_ALGO', 'aes-256-cbc'),
    
    // Enable compression before upload
    'enable_compression' => env('AI_FILTER_ENABLE_COMPRESSION', true),
    
    // Compression level (1-9, where 9 is maximum compression)
    'compression_level' => env('AI_FILTER_COMPRESSION_LEVEL', 6),
    
    /*
    |--------------------------------------------------------------------------
    | PII Detection Patterns
    |--------------------------------------------------------------------------
    |
    | Regex patterns for detecting various types of PII
    | 
    */
    
    'pii_patterns' => [
        'credit_card' => '/\b(?:\d{4}[\s\-]?){3}\d{4}\b/',
        'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'phone' => '/\b(?:\+?1[-.\s]?)?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})\b/',
        'account' => '/\b(?:account|acct)[\s#:]*(\d{8,12})\b/i',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Transcript Cleaning Patterns (Layer 1)
    |--------------------------------------------------------------------------
    |
    | Patterns for cleaning transcripts as per Layer 1 requirements
    |
    */
    
    'cleaning_patterns' => [
        'timestamp' => '/\b\d{1,2}:\d{2}:\d{2}\b/',
        'speaker' => '/(?i)\b(agent|customer)\b/',
        'filler_words' => '/\b(uh|um)\b/i',
        'noise_tags' => '/\[(noise|silence)\]/i',
    ],
    
];
