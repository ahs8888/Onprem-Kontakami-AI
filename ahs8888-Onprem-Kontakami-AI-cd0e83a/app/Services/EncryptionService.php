<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * Encryption Service for Recording Files
 * 
 * Provides AES-256 encryption/decryption for files before cloud upload
 * Uses symmetric key encryption (same key on on-prem and cloud)
 */
class EncryptionService
{
    private string $algorithm;
    private string $key;
    
    public function __construct()
    {
        $this->algorithm = config('ai_filter.encryption_algorithm', 'aes-256-cbc');
        $this->key = $this->getEncryptionKey();
    }
    
    /**
     * Encrypt a file
     * 
     * @param string $inputPath Path to file to encrypt
     * @param string|null $outputPath Path for encrypted file (optional)
     * @return array ['success', 'encrypted_path', 'original_size', 'encrypted_size']
     */
    public function encryptFile(string $inputPath, ?string $outputPath = null): array
    {
        try {
            if (!file_exists($inputPath)) {
                throw new \Exception("Input file not found: {$inputPath}");
            }
            
            // Read file content
            $data = file_get_contents($inputPath);
            $originalSize = strlen($data);
            
            // Generate IV (Initialization Vector)
            $ivLength = openssl_cipher_iv_length($this->algorithm);
            $iv = openssl_random_pseudo_bytes($ivLength);
            
            // Encrypt data
            $encrypted = openssl_encrypt(
                $data,
                $this->algorithm,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($encrypted === false) {
                throw new \Exception("Encryption failed");
            }
            
            // Prepend IV to encrypted data (needed for decryption)
            $encryptedWithIv = $iv . $encrypted;
            
            // Determine output path
            if ($outputPath === null) {
                $outputPath = $inputPath . '.enc';
            }
            
            // Write encrypted file
            file_put_contents($outputPath, $encryptedWithIv);
            
            $encryptedSize = filesize($outputPath);
            
            Log::info("File encrypted successfully", [
                'input' => $inputPath,
                'output' => $outputPath,
                'original_size' => $originalSize,
                'encrypted_size' => $encryptedSize
            ]);
            
            return [
                'success' => true,
                'encrypted_path' => $outputPath,
                'original_size' => $originalSize,
                'encrypted_size' => $encryptedSize,
                'iv_length' => $ivLength,
                'algorithm' => $this->algorithm
            ];
            
        } catch (\Exception $e) {
            Log::error("File encryption failed", [
                'error' => $e->getMessage(),
                'file' => $inputPath
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Decrypt a file
     * 
     * @param string $inputPath Path to encrypted file
     * @param string|null $outputPath Path for decrypted file (optional)
     * @return array ['success', 'decrypted_path', 'size']
     */
    public function decryptFile(string $inputPath, ?string $outputPath = null): array
    {
        try {
            if (!file_exists($inputPath)) {
                throw new \Exception("Input file not found: {$inputPath}");
            }
            
            // Read encrypted file
            $encryptedWithIv = file_get_contents($inputPath);
            
            // Extract IV
            $ivLength = openssl_cipher_iv_length($this->algorithm);
            $iv = substr($encryptedWithIv, 0, $ivLength);
            $encrypted = substr($encryptedWithIv, $ivLength);
            
            // Decrypt data
            $decrypted = openssl_decrypt(
                $encrypted,
                $this->algorithm,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv
            );
            
            if ($decrypted === false) {
                throw new \Exception("Decryption failed");
            }
            
            // Determine output path
            if ($outputPath === null) {
                $outputPath = str_replace('.enc', '', $inputPath);
            }
            
            // Write decrypted file
            file_put_contents($outputPath, $decrypted);
            
            Log::info("File decrypted successfully", [
                'input' => $inputPath,
                'output' => $outputPath,
                'size' => strlen($decrypted)
            ]);
            
            return [
                'success' => true,
                'decrypted_path' => $outputPath,
                'size' => strlen($decrypted)
            ];
            
        } catch (\Exception $e) {
            Log::error("File decryption failed", [
                'error' => $e->getMessage(),
                'file' => $inputPath
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Compress and encrypt file
     */
    public function compressAndEncrypt(string $inputPath, ?string $outputPath = null): array
    {
        try {
            if (!config('ai_filter.enable_compression', true)) {
                return $this->encryptFile($inputPath, $outputPath);
            }
            
            // Read file
            $data = file_get_contents($inputPath);
            $originalSize = strlen($data);
            
            // Compress using gzip
            $compressionLevel = config('ai_filter.compression_level', 6);
            $compressed = gzcompress($data, $compressionLevel);
            $compressedSize = strlen($compressed);
            
            // Save compressed file temporarily
            $tempCompressed = $inputPath . '.gz';
            file_put_contents($tempCompressed, $compressed);
            
            // Encrypt compressed file
            $result = $this->encryptFile($tempCompressed, $outputPath);
            
            // Clean up temp file
            @unlink($tempCompressed);
            
            if ($result['success']) {
                $result['original_size'] = $originalSize;
                $result['compressed_size'] = $compressedSize;
                $result['compression_ratio'] = round(($originalSize - $compressedSize) / $originalSize * 100, 2);
            }
            
            Log::info("File compressed and encrypted", [
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'encrypted_size' => $result['encrypted_size'] ?? 0,
                'compression_ratio' => $result['compression_ratio'] ?? 0
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error("Compress and encrypt failed", [
                'error' => $e->getMessage(),
                'file' => $inputPath
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Decrypt and decompress file
     */
    public function decryptAndDecompress(string $inputPath, ?string $outputPath = null): array
    {
        try {
            // First decrypt
            $tempDecrypted = $inputPath . '.dec';
            $decryptResult = $this->decryptFile($inputPath, $tempDecrypted);
            
            if (!$decryptResult['success']) {
                return $decryptResult;
            }
            
            if (!config('ai_filter.enable_compression', true)) {
                // If compression not enabled, just rename
                if ($outputPath) {
                    rename($tempDecrypted, $outputPath);
                    return ['success' => true, 'decrypted_path' => $outputPath];
                }
                return $decryptResult;
            }
            
            // Decompress
            $compressed = file_get_contents($tempDecrypted);
            $decompressed = gzuncompress($compressed);
            
            // Determine output path
            if ($outputPath === null) {
                $outputPath = str_replace('.enc', '', $inputPath);
            }
            
            // Write decompressed file
            file_put_contents($outputPath, $decompressed);
            
            // Clean up temp file
            @unlink($tempDecrypted);
            
            Log::info("File decrypted and decompressed", [
                'output' => $outputPath,
                'size' => strlen($decompressed)
            ]);
            
            return [
                'success' => true,
                'decrypted_path' => $outputPath,
                'size' => strlen($decompressed)
            ];
            
        } catch (\Exception $e) {
            Log::error("Decrypt and decompress failed", [
                'error' => $e->getMessage(),
                'file' => $inputPath
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get encryption key from environment
     */
    private function getEncryptionKey(): string
    {
        $key = env('FILE_ENCRYPTION_KEY');
        
        if (empty($key)) {
            // Generate a key if not set (for development)
            Log::warning("FILE_ENCRYPTION_KEY not set, using generated key");
            $key = base64_encode(openssl_random_pseudo_bytes(32));
        }
        
        // Ensure key is proper length for algorithm
        if ($this->algorithm === 'aes-256-cbc') {
            $key = substr(hash('sha256', $key, true), 0, 32);
        } elseif ($this->algorithm === 'aes-128-cbc') {
            $key = substr(hash('sha256', $key, true), 0, 16);
        }
        
        return $key;
    }
    
    /**
     * Generate a new encryption key
     */
    public static function generateKey(): string
    {
        return base64_encode(openssl_random_pseudo_bytes(32));
    }
}
