<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AIFilterService;
use App\Models\Data\RecordingDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AIFilterServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AIFilterService $aiFilterService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiFilterService = app(AIFilterService::class);
    }

    /**
     * Test AI filter evaluates high quality recording
     */
    public function test_approves_high_quality_recording(): void
    {
        $recording = RecordingDetail::factory()->create([
            'transcript' => 'This is a clear and professional customer service call.',
            'confidence_score' => 0.95,
            'quality_score' => 0.88,
        ]);

        $result = $this->aiFilterService->evaluateRecording($recording);

        $this->assertEquals('upload_approved', $result['decision']);
        $this->assertTrue($result['should_upload']);
        $this->assertGreaterThanOrEqual(0.7, $result['confidence']);
    }

    /**
     * Test AI filter rejects low quality recording
     */
    public function test_rejects_low_quality_recording(): void
    {
        $recording = RecordingDetail::factory()->create([
            'transcript' => 'uh... um... [NOISE] can\'t hear... [STATIC]',
            'confidence_score' => 0.45,
            'quality_score' => 0.32,
        ]);

        $result = $this->aiFilterService->evaluateRecording($recording);

        $this->assertEquals('rejected_low_quality', $result['decision']);
        $this->assertFalse($result['should_upload']);
    }

    /**
     * Test PII detection
     */
    public function test_detects_pii_in_transcript(): void
    {
        $recording = RecordingDetail::factory()->create([
            'transcript' => 'My social security number is 123-45-6789 and my email is john@example.com',
            'confidence_score' => 0.85,
            'quality_score' => 0.80,
        ]);

        $result = $this->aiFilterService->detectPII($recording);

        $this->assertTrue($result['pii_detected']);
        $this->assertContains('ssn', $result['pii_types']);
        $this->assertContains('email', $result['pii_types']);
    }

    /**
     * Test PII redaction
     */
    public function test_redacts_pii_from_transcript(): void
    {
        $transcript = 'My SSN is 123-45-6789';
        
        $redacted = $this->aiFilterService->redactPII($transcript);

        $this->assertStringNotContainsString('123-45-6789', $redacted);
        $this->assertStringContainsString('[REDACTED]', $redacted);
    }

    /**
     * Test confidence score calculation
     */
    public function test_calculates_confidence_score(): void
    {
        $recording = RecordingDetail::factory()->create([
            'transcript' => 'Clear professional speech',
            'duration' => 120,
        ]);

        $score = $this->aiFilterService->calculateConfidenceScore($recording);

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(1, $score);
    }

    /**
     * Test quality score calculation
     */
    public function test_calculates_quality_score(): void
    {
        $recording = RecordingDetail::factory()->create([
            'transcript' => 'This is a high quality recording with clear audio',
            'duration' => 180,
        ]);

        $score = $this->aiFilterService->calculateQualityScore($recording);

        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(1, $score);
    }

    /**
     * Test AI decision with PII
     */
    public function test_flags_recording_with_pii(): void
    {
        config(['ai_filter.enable_pii_detection' => true]);
        
        $recording = RecordingDetail::factory()->create([
            'transcript' => 'My credit card is 4532-1234-5678-9010',
            'confidence_score' => 0.80,
            'quality_score' => 0.75,
        ]);

        $result = $this->aiFilterService->evaluateRecording($recording);

        $this->assertTrue($result['pii_detected']);
        $this->assertEquals('flagged_pii', $result['decision']);
    }

    /**
     * Test configuration thresholds
     */
    public function test_respects_configuration_thresholds(): void
    {
        config(['ai_filter.min_confidence' => 0.9]);
        config(['ai_filter.min_quality' => 0.85]);

        $recording = RecordingDetail::factory()->create([
            'transcript' => 'Good recording',
            'confidence_score' => 0.80, // Below threshold
            'quality_score' => 0.80,    // Below threshold
        ]);

        $result = $this->aiFilterService->evaluateRecording($recording);

        $this->assertFalse($result['should_upload']);
    }
}
