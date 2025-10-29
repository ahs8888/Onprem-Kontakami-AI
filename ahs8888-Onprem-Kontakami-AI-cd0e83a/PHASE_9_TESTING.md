# Phase 9 Testing Guide: AI Micro-Decisions

## Test Sample Recordings

Create test audio files with varying quality levels to test the AI filtering system.

### Test Scenario 1: High Quality Recording (Should Upload)

**File:** `test_high_quality.wav`

**Simulated Transcript:**
```
00:00:10 Agent: Hello, thank you for calling customer support. My name is Sarah.
00:00:15 Customer: Hi Sarah, I need help with my account.
00:00:20 Agent: I'd be happy to help. Can you describe the issue?
00:00:25 Customer: I can't log into my online banking.
00:00:30 Agent: Let me pull up your account and assist you with that.
```

**Expected Results:**
- ✅ Confidence Score: >70%
- ✅ Quality Score: >60%
- ✅ No PII detected
- ✅ Duration: appropriate
- ✅ Decision: **APPROVED for upload**
- ✅ Status: "Success"
- ✅ File encrypted before upload

### Test Scenario 2: Low Quality Recording (Should NOT Upload)

**File:** `test_low_quality.wav`

**Simulated Transcript:**
```
[noise] uh um [silence] can't hear [noise] what [silence] uh sorry um [noise]
```

**Expected Results:**
- ❌ Confidence Score: <70%
- ❌ Quality Score: <60%
- ❌ High filler words (uh, um)
- ❌ High noise tags
- ❌ Decision: **REJECTED**
- ❌ Status: "Filtered"
- ❌ Recommended Action: "re-record"
- ❌ NOT uploaded to cloud

### Test Scenario 3: Recording with PII (Should Redact)

**File:** `test_with_pii.wav`

**Simulated Transcript:**
```
00:00:05 Agent: Can I have your account number please?
00:00:10 Customer: Yes, it's account 123456789012.
00:00:15 Customer: My credit card is 4532-1234-5678-9012.
00:00:20 Customer: Email is john.doe@example.com
00:00:25 Agent: Thank you, let me process that for you.
```

**Expected Results:**
- ✅ Confidence Score: >70%
- ✅ Quality Score: >60%
- ⚠️ PII Detected: credit card, email, account number
- ⚠️ PII Redacted in transcript
- ✅ Decision: **APPROVED with redaction**
- ✅ Status: "Success"

**Redacted Transcript:**
```
00:00:05 Agent: Can I have your account number please?
00:00:10 Customer: Yes, it's account [REDACTED-ACCOUNT].
00:00:15 Customer: My credit card is [REDACTED-CC].
00:00:20 Customer: Email is [REDACTED-EMAIL]
00:00:25 Agent: Thank you, let me process that for you.
```

### Test Scenario 4: Too Short Recording (Should Discard)

**File:** `test_too_short.wav`

**Simulated Transcript:**
```
Hello? Hello?
```

**Expected Results:**
- ❌ Duration: <30 seconds
- ❌ Decision: **REJECTED**
- ❌ Recommended Action: "discard"
- ❌ Reason: "Duration too short"
- ❌ NOT uploaded to cloud

### Test Scenario 5: Perfect Recording (Golden Standard)

**File:** `test_perfect.wav`

**Simulated Transcript:**
```
00:00:05 Agent: Good morning, thank you for calling. My name is David.
00:00:10 Customer: Hi David, I have a question about my recent order.
00:00:15 Agent: I'd be happy to help. Can you provide your order number?
00:00:20 Customer: Sure, it's order number ABC123.
00:00:25 Agent: Let me look that up for you.
00:00:30 Customer: Thank you.
00:00:35 Agent: I see your order here. It was shipped yesterday.
00:00:40 Customer: Great, when should I expect delivery?
00:00:45 Agent: You should receive it within three to five business days.
00:00:50 Customer: Perfect, thank you for your help.
00:00:55 Agent: You're welcome. Have a great day!
```

**Expected Results:**
- ✅ Confidence Score: 95%+
- ✅ Quality Score: 90%+
- ✅ Timestamps detected: 10+
- ✅ Speakers detected: Agent, Customer
- ✅ Balanced conversation
- ✅ No filler words
- ✅ No noise tags
- ✅ No PII
- ✅ Appropriate duration
- ✅ Decision: **APPROVED**
- ✅ Encrypted and compressed
- ✅ Uploaded to cloud

## Testing Procedure

### 1. Upload Test Recordings

```bash
# Navigate to your app
cd /app/ahs8888-Onprem-Kontakami-AI-cd0e83a

# Start services
php artisan serve &
npm run dev &
php artisan queue:work &
```

**Via Browser:**
1. Go to http://localhost:8000/recordings
2. Click "New Recording"
3. Check "Requires Ticket"
4. Upload test folder with all 5 test files
5. Monitor queue worker output

### 2. Monitor Processing

**Watch logs in real-time:**
```bash
tail -f storage/logs/laravel.log | grep -E "(Layer 1|AI Filter|File encrypted)"
```

**Expected log entries:**

```
[2025-01-29 10:00:00] Layer 1 - Transcript cleaned
  - recording: test_high_quality.wav
  - confidence: 85.5
  - filler_removed: 0
  - noise_removed: 0

[2025-01-29 10:00:01] AI Filter Decision
  - recording: test_high_quality.wav
  - should_upload: true
  - reason: All quality checks passed
  - confidence: 85.5
  - quality: 87.2
  - pii_detected: false
  - action: upload

[2025-01-29 10:00:02] File encrypted and compressed
  - recording: test_high_quality.wav
  - original_size: 2621440
  - encrypted_size: 1835008
  - compression_ratio: 30%
```

### 3. Check Database

```sql
-- Check AI scores
SELECT 
    name,
    confidence_score,
    quality_score,
    upload_decision,
    decision_reason,
    pii_detected,
    is_encrypted,
    status
FROM recording_details
ORDER BY created_at DESC
LIMIT 10;

-- Expected results:
-- test_high_quality.wav: approved, 85.5, 87.2, Success
-- test_low_quality.wav: rejected, 45.0, 40.0, Filtered
-- test_with_pii.wav: approved, 88.0, 85.0, Success (pii_detected=1)
-- test_too_short.wav: rejected, 50.0, 30.0, Filtered
-- test_perfect.wav: approved, 95.0, 92.0, Success
```

### 4. Test Telemetry Endpoint

```bash
curl http://localhost:8000/status/local
```

**Expected JSON response:**
```json
{
  "success": true,
  "data": {
    "timestamp": "2025-01-29T10:00:00Z",
    "queue": {
      "pending_jobs": 0,
      "failed_jobs": 0,
      "recordings_in_progress": 0,
      "recordings_pending_stt": 0,
      "queue_health": "healthy"
    },
    "bandwidth": {
      "last_hour": {
        "uploads": 3,
        "bandwidth_mb": 7.5,
        "rate_mbps": 0.0021
      },
      "today": {
        "uploads": 3,
        "bandwidth_mb": 7.5,
        "bandwidth_gb": 0.01
      }
    },
    "recordings": {
      "total": 5,
      "processed": 5,
      "uploaded": 3,
      "failed": 0,
      "success_rate": 100,
      "upload_rate": 60,
      "quality_distribution": {
        "high": 2,
        "medium": 1,
        "low": 2
      },
      "pii_detected": 1
    },
    "quality": {
      "avg_confidence_score": 72.8,
      "avg_quality_score": 68.5,
      "auto_filtered_out": 2,
      "auto_approved": 3,
      "filter_rate": 40
    }
  }
}
```

### 5. Verify Filtering Logic

**Check that:**
- [ ] 3 out of 5 recordings uploaded (60% upload rate)
- [ ] 2 out of 5 recordings filtered (40% filter rate)
- [ ] PII detected in 1 recording
- [ ] All uploaded files are encrypted
- [ ] Bandwidth savings achieved

**Calculate savings:**
```
Total recordings: 5
Without filtering: 5 uploads × 2.5 MB = 12.5 MB
With filtering: 3 uploads × 2.5 MB = 7.5 MB
Savings: 5 MB (40%)
```

### 6. Test Encryption/Decryption

**Encrypt a test file:**
```bash
php artisan tinker
>>> $enc = new \App\Services\EncryptionService();
>>> $result = $enc->compressAndEncrypt('/path/to/test.wav');
>>> print_r($result);
exit
```

**Verify encrypted file exists:**
```bash
ls -lh /path/to/test.wav.enc
```

**Decrypt it back:**
```bash
php artisan tinker
>>> $enc = new \App\Services\EncryptionService();
>>> $result = $enc->decryptAndDecompress('/path/to/test.wav.enc', '/path/to/test_decrypted.wav');
>>> print_r($result);
exit
```

**Compare files:**
```bash
diff /path/to/test.wav /path/to/test_decrypted.wav
# Should be identical
```

### 7. Test Threshold Configuration

**Adjust thresholds in .env:**
```env
# Make filtering more strict
AI_FILTER_MIN_CONFIDENCE=90.0
AI_FILTER_MIN_QUALITY_SCORE=80.0
```

**Restart queue worker:**
```bash
php artisan queue:restart
php artisan queue:work
```

**Upload same test files again:**
- Expect: More recordings filtered out
- Only test_perfect.wav should pass

### 8. Load Testing

**Upload 100 recordings:**
```bash
# Create test script
php artisan tinker
>>> for ($i = 1; $i <= 100; $i++) {
...     \App\Jobs\ProcessRecordingBatch::dispatch('test-recording-id');
... }
exit
```

**Monitor performance:**
```bash
# Watch queue
watch -n 1 'mysql -u root -p -e "SELECT COUNT(*) FROM your_db.jobs"'

# Watch telemetry
watch -n 5 'curl -s http://localhost:8000/status/local | jq .data.queue'
```

## Success Criteria

### Functional Tests
- [ ] High quality recordings approved and uploaded
- [ ] Low quality recordings rejected
- [ ] PII automatically detected and redacted
- [ ] Short recordings discarded
- [ ] Files encrypted before upload
- [ ] Compression working (20-40% size reduction)

### Performance Tests
- [ ] Processing time < 30 seconds per recording
- [ ] Encryption adds < 5 seconds overhead
- [ ] Bandwidth saved 30-50%
- [ ] Queue handles 100+ jobs smoothly

### Monitoring Tests
- [ ] `/status/local` endpoint responds
- [ ] `/status/health` returns correct status
- [ ] Telemetry logs created in database
- [ ] All metrics accurate

### Integration Tests
- [ ] Approved recordings reach cloud
- [ ] Rejected recordings NOT sent to cloud
- [ ] Cloud receives encrypted files
- [ ] Ticket info included in upload
- [ ] Cloud can decrypt files (test separately)

## Troubleshooting

**Issue:** All recordings rejected
```bash
# Check thresholds
cat .env | grep AI_FILTER

# Lower thresholds temporarily
AI_FILTER_MIN_CONFIDENCE=50.0
AI_FILTER_MIN_QUALITY_SCORE=40.0
```

**Issue:** PII not detected
```bash
# Check regex patterns in config/ai_filter.php
# Test manually:
php artisan tinker
>>> $service = new \App\Services\AIFilterService(new \App\Services\TranscriptCleaningService());
>>> $result = $service->detectPII("My card is 4532-1234-5678-9012");
>>> print_r($result);
```

**Issue:** Encryption fails
```bash
# Check encryption key
cat .env | grep FILE_ENCRYPTION_KEY

# Regenerate if needed
php artisan tinker
>>> echo \App\Services\EncryptionService::generateKey();
```

**Issue:** Telemetry endpoint 404
```bash
# Clear caches
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Verify route exists
php artisan route:list | grep telemetry
```

## Next Steps

After successful testing:
1. ✅ Adjust thresholds based on results
2. ✅ Deploy to production
3. ✅ Monitor for 24 hours
4. ✅ Analyze filter effectiveness
5. ✅ Fine-tune as needed
