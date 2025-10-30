<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Storage;

class EncryptionServiceTest extends TestCase
{
    protected EncryptionService $encryptionService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->encryptionService = app(EncryptionService::class);
    }

    /**
     * Test file encryption
     */
    public function test_can_encrypt_file(): void
    {
        $originalContent = 'This is test audio data';
        $filePath = 'test/recording.wav';
        
        Storage::put($filePath, $originalContent);

        $encryptedPath = $this->encryptionService->encryptFile($filePath);

        $this->assertNotNull($encryptedPath);
        $this->assertTrue(Storage::exists($encryptedPath));
        
        $encryptedContent = Storage::get($encryptedPath);
        $this->assertNotEquals($originalContent, $encryptedContent);
    }

    /**
     * Test file decryption
     */
    public function test_can_decrypt_file(): void
    {
        $originalContent = 'This is test audio data';
        $filePath = 'test/recording.wav';
        
        Storage::put($filePath, $originalContent);

        // Encrypt
        $encryptedPath = $this->encryptionService->encryptFile($filePath);
        
        // Decrypt
        $decryptedPath = $this->encryptionService->decryptFile($encryptedPath);
        
        $this->assertNotNull($decryptedPath);
        $this->assertTrue(Storage::exists($decryptedPath));
        
        $decryptedContent = Storage::get($decryptedPath);
        $this->assertEquals($originalContent, $decryptedContent);
    }

    /**
     * Test encryption method
     */
    public function test_uses_aes_256_encryption(): void
    {
        $method = $this->encryptionService->getEncryptionMethod();
        
        $this->assertEquals('aes-256-cbc', $method);
    }

    /**
     * Test batch compression
     */
    public function test_can_compress_files(): void
    {
        $files = [
            'test/file1.wav',
            'test/file2.wav',
        ];

        foreach ($files as $file) {
            Storage::put($file, 'Test content for ' . $file);
        }

        $compressedPath = $this->encryptionService->compressBatch($files);

        $this->assertNotNull($compressedPath);
        $this->assertTrue(Storage::exists($compressedPath));
        $this->assertStringEndsWith('.zip', $compressedPath);
    }

    /**
     * Test encryption with compression
     */
    public function test_can_encrypt_and_compress(): void
    {
        $originalContent = str_repeat('This is test data. ', 100);
        $filePath = 'test/large_recording.wav';
        
        Storage::put($filePath, $originalContent);

        $result = $this->encryptionService->encryptAndCompress($filePath);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('encrypted_path', $result);
        $this->assertArrayHasKey('compressed', $result);
        $this->assertTrue($result['compressed']);
    }

    /**
     * Test encryption metadata
     */
    public function test_returns_encryption_metadata(): void
    {
        $filePath = 'test/recording.wav';
        Storage::put($filePath, 'Test content');

        $metadata = $this->encryptionService->getEncryptionMetadata($filePath);

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('method', $metadata);
        $this->assertArrayHasKey('encrypted_at', $metadata);
        $this->assertArrayHasKey('original_size', $metadata);
    }
}
