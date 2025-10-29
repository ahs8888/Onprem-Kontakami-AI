# Phase 9 Setup Instructions

## Step 1: Run Migrations

Once your Laravel environment is set up, run:

```bash
cd /app/ahs8888-Onprem-Kontakami-AI-cd0e83a
php artisan migrate
```

This will create:
- New columns in `recording_details` table for AI quality tracking
- New `telemetry_logs` table for monitoring

## Step 2: Generate and Set Encryption Key

### Generate Key:

```bash
php artisan tinker
```

Then in tinker console:
```php
echo App\Services\EncryptionService::generateKey();
exit
```

Copy the output (e.g., `dGhpc2lzYXNhbXBsZWVuY3J5cHRpb25rZXk=`)

### Add to .env:

```env
# Add this to your .env file
FILE_ENCRYPTION_KEY=dGhpc2lzYXNhbXBsZWVuY3J5cHRpb25rZXk=

# AI Filter Configuration (optional, defaults provided)
AI_FILTER_MIN_CONFIDENCE=70.0
AI_FILTER_MIN_QUALITY_SCORE=60.0
AI_FILTER_MIN_DURATION=30
AI_FILTER_MAX_DURATION=3600
AI_FILTER_BLOCK_PII=true
AI_FILTER_REDACT_PII=true
AI_FILTER_MAX_RETRIES=1
AI_FILTER_ENABLE_ENCRYPTION=true
AI_FILTER_ENABLE_COMPRESSION=true
AI_FILTER_COMPRESSION_LEVEL=6
```

### Important: Cloud App Must Have Same Key

Copy the same `FILE_ENCRYPTION_KEY` to your cloud app's `.env` so it can decrypt files.

## Step 3: Integration Complete

The `ProcessRecordingBatch` job has been updated to use all AI micro-decision services.

## Step 4: Test the System

### Test 1: Upload with Quality Check

1. Navigate to Recordings page
2. Click "New Recording"
3. Check "Requires Ticket"
4. Upload test recordings
5. Check logs to see AI filtering in action

### Test 2: Check Telemetry

```bash
# Via browser
http://localhost:8000/status/local

# Or via curl
curl http://localhost:8000/status/local
```

### Test 3: Check Encryption

```bash
# Check if files are encrypted
ls -lh storage/app/public/uploads/

# Files with .enc extension are encrypted
```

## Expected Behavior:

1. **High Quality Recording:**
   - Confidence: 85%
   - Duration: 2 minutes
   - No PII
   - ✅ Uploaded to cloud

2. **Low Quality Recording:**
   - Confidence: 45%
   - Duration: 1 minute
   - Poor audio
   - ❌ Flagged for re-record, NOT uploaded

3. **Recording with PII:**
   - Confidence: 90%
   - Contains credit card number
   - ⚠️ PII redacted, then uploaded

## Monitoring:

Check logs:
```bash
tail -f storage/logs/laravel.log
```

Look for:
- "AI Filter Decision"
- "File encrypted successfully"
- "Recording quality score"
- "PII detected"

## Troubleshooting:

**Issue:** Migrations fail
```bash
# Check database connection
php artisan db:show

# Run specific migration
php artisan migrate --path=database/migrations/2025_01_29_100000_add_ai_quality_fields_to_recording_details.php
```

**Issue:** Encryption key error
```bash
# Regenerate key
php artisan tinker
>>> App\Services\EncryptionService::generateKey()
```

**Issue:** Telemetry endpoint 404
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache
```
