<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TranscriptCleaningService;

class TranscriptCleaningServiceTest extends TestCase
{
    protected TranscriptCleaningService $cleaningService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleaningService = app(TranscriptCleaningService::class);
    }

    /**
     * Test filler word removal
     */
    public function test_removes_filler_words(): void
    {
        $transcript = 'Um, well, you know, like, I think we should, uh, proceed with this.';
        
        $cleaned = $this->cleaningService->clean($transcript);

        $this->assertStringNotContainsString('um', strtolower($cleaned));
        $this->assertStringNotContainsString('uh', strtolower($cleaned));
        $this->assertStringNotContainsString('you know', strtolower($cleaned));
    }

    /**
     * Test noise tag removal
     */
    public function test_removes_noise_tags(): void
    {
        $transcript = 'Hello [NOISE] how are you [STATIC] doing today [COUGH]?';
        
        $cleaned = $this->cleaningService->clean($transcript);

        $this->assertStringNotContainsString('[NOISE]', $cleaned);
        $this->assertStringNotContainsString('[STATIC]', $cleaned);
        $this->assertStringNotContainsString('[COUGH]', $cleaned);
    }

    /**
     * Test multiple space normalization
     */
    public function test_normalizes_multiple_spaces(): void
    {
        $transcript = 'Hello    there    how   are    you?';
        
        $cleaned = $this->cleaningService->clean($transcript);

        $this->assertStringNotContainsString('  ', $cleaned);
        $this->assertEquals('Hello there how are you?', $cleaned);
    }

    /**
     * Test timestamp detection
     */
    public function test_detects_timestamps(): void
    {
        $transcript = '[00:15] Agent: Hello [00:20] Customer: Hi there';
        
        $result = $this->cleaningService->detectTimestamps($transcript);

        $this->assertTrue($result['has_timestamps']);
        $this->assertCount(2, $result['timestamps']);
    }

    /**
     * Test speaker detection
     */
    public function test_detects_speakers(): void
    {
        $transcript = 'Agent: Hello, how can I help? Customer: I need assistance.';
        
        $result = $this->cleaningService->detectSpeakers($transcript);

        $this->assertTrue($result['has_speakers']);
        $this->assertContains('Agent', $result['speakers']);
        $this->assertContains('Customer', $result['speakers']);
    }

    /**
     * Test cleaning metadata
     */
    public function test_returns_cleaning_metadata(): void
    {
        $transcript = 'Um, hello [NOISE] this is, uh, a test.';
        
        $result = $this->cleaningService->cleanWithMetadata($transcript);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('cleaned_transcript', $result);
        $this->assertArrayHasKey('removed_words', $result);
        $this->assertArrayHasKey('removed_noise', $result);
        $this->assertGreaterThan(0, $result['removed_words']);
    }

    /**
     * Test preserves important content
     */
    public function test_preserves_important_content(): void
    {
        $transcript = 'The customer\'s account number is 123456 and the issue is billing.';
        
        $cleaned = $this->cleaningService->clean($transcript);

        $this->assertStringContainsString('123456', $cleaned);
        $this->assertStringContainsString('account number', $cleaned);
        $this->assertStringContainsString('billing', $cleaned);
    }
}
