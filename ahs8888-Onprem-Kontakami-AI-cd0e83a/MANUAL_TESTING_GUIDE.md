# Manual Testing Guide - On-Prem Kontakami AI

This comprehensive guide walks you through testing all features of the On-Prem application, especially the Phase 9 AI micro-decisions and quality control features.

## Prerequisites

✅ Docker Compose environment is running (see `DOCKER_SETUP_README.md`)
✅ Database migrations completed
✅ Gemini API key configured in `.env`
✅ Application accessible at http://localhost:8080

## Testing Checklist

- [ ] **Test Suite 1**: Basic Application Setup
- [ ] **Test Suite 2**: Recording Upload & STT
- [ ] **Test Suite 3**: Ticket Linking System
- [ ] **Test Suite 4**: AI Quality Evaluation
- [ ] **Test Suite 5**: PII Detection & Privacy
- [ ] **Test Suite 6**: Selective Upload Logic
- [ ] **Test Suite 7**: Encryption & Compression
- [ ] **Test Suite 8**: Telemetry Monitoring
- [ ] **Test Suite 9**: Queue Processing
- [ ] **Test Suite 10**: End-to-End Workflow

---

## Test Suite 1: Basic Application Setup

### 1.1 Verify Services Are Running

```bash
# Check all containers are up
docker compose ps

# Expected: All services should be "Up" status
```

### 1.2 Access the Application

1. Open browser: http://localhost:8080
2. **Expected**: Laravel welcome page or login page loads
3. Check browser console for errors (F12)
4. **Expected**: No JavaScript errors

### 1.3 Verify Database Connection

```bash
# Check database connectivity
docker compose exec app php artisan db:show

# Expected: MySQL connection details displayed
```

### 1.4 Check Queue Worker

```bash
# View queue worker logs
docker compose logs -f queue

# Expected: "Processing jobs" messages appear
```

✅ **Success Criteria**: All services running, application loads, database connected

---

## Test Suite 2: Recording Upload & STT

### 2.1 Prepare Test Audio File

Create a simple test recording or use an existing one:
- Format: .wav, .mp3, or .m4a
- Duration: 30-60 seconds
- Content: Clear speech in English

**Sample filename format**: `AGENTXYZ_20250129_140530.wav`

### 2.2 Upload Recording via UI

1. Navigate to **Recordings** page
2. Click **Upload Recording** button
3. Select your test audio file
4. **Modal appears**: "Does this recording require ticket linking?"
   - For this test, select **"No, proceed without ticket"**
5. Click **Upload**

**Expected Results**:
- Upload progress bar shows
- Success message appears
- Recording appears in the recordings list with status: `processing`

### 2.3 Verify STT Processing

```bash
# Watch queue logs for STT processing
docker compose logs -f queue

# Expected log entries:
# - "Processing RecordingAction for recording ID: X"
# - "STT completed successfully"
# - "Transcript saved"
```

### 2.4 Check Recording Details

1. In UI, click on the uploaded recording
2. **Expected fields**:
   - **Filename**: Original filename
   - **Status**: `completed` or `processed`
   - **Transcript**: Text from audio
   - **Duration**: Audio length in seconds
   - **Confidence Score**: Between 0 and 1
   - **Quality Score**: Between 0 and 1

### 2.5 Verify Database Entry

```bash
# Check database record
docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
  SELECT id, file_name, status, confidence_score, quality_score 
  FROM recording_details 
  ORDER BY created_at DESC 
  LIMIT 1;
"
```

**Expected**: Record with populated confidence and quality scores

✅ **Success Criteria**: Recording uploaded, STT completed, scores calculated, transcript visible

---

## Test Suite 3: Ticket Linking System

### 3.1 Prepare Test Ticket Data

Sample CSV file already exists: `/test-data/sample_tickets_valid.csv`

**Content example**:
```csv
Ticket ID,Customer Name,Agent Name,Intent,Outcome,Ticket URL
TICKET001,John Doe,Agent Smith,Billing,Resolved,https://tickets.example.com/001
TICKET002,Jane Smith,Agent Jones,Technical,Pending,https://tickets.example.com/002
```

### 3.2 Import Ticket Data

1. Navigate to **Ticket Import** page
2. **Step 1: Upload CSV**
   - Click **Choose File**
   - Select `/test-data/sample_tickets_valid.csv`
   - Click **Upload & Validate**

3. **Step 2: Map Columns**
   - System auto-detects headers
   - Verify mappings:
     - `Ticket ID` → Ticket ID
     - `Customer Name` → Customer Name
     - `Agent Name` → Agent Name
     - `Intent` → Intent
     - `Outcome` → Outcome
     - `Ticket URL` → Ticket URL
   - Click **Confirm Mapping**

4. **Step 3: Validate Data**
   - Review validation results
   - **Expected**: X rows valid, 0 errors
   - Click **Proceed to Import**

5. **Step 4: Import**
   - Click **Start Import**
   - **Expected**: Success message with count of imported tickets

### 3.3 Verify Ticket Linking

1. Upload a recording with filename: `TICKET001_customer_call.wav`
2. In upload modal, select **"Yes, this requires a ticket"**
3. After upload, view recording details

**Expected fields**:
- **Ticket ID**: TICKET001
- **Customer Name**: John Doe
- **Agent Name**: Agent Smith
- **Intent**: Billing
- **Outcome**: Resolved
- **Ticket URL**: Clickable link
- **Status**: `linked`

### 3.4 Test Unlinked Recording

1. Upload recording: `UNKNOWN_TICKET_call.wav`
2. Select **"Yes, this requires a ticket"**
3. After upload, check status

**Expected**:
- **Status**: `unlinked`
- **Banner/Warning**: "This recording requires ticket linking but no match found"

### 3.5 View Ticket Statistics

On Recordings page, check **Ticket Stats** component:

**Expected display**:
- Total Recordings: X
- Linked: Y
- Unlinked: Z
- No Ticket Needed: W

✅ **Success Criteria**: Tickets imported, recordings linked correctly, stats accurate

---

## Test Suite 4: AI Quality Evaluation

### 4.1 Configure AI Thresholds

Edit `.env` file:

```bash
AI_MIN_CONFIDENCE=0.7
AI_MIN_QUALITY=0.6
```

Restart services:
```bash
docker compose restart app queue
```

### 4.2 Test High-Quality Recording

1. Upload a clear, well-recorded audio file
2. Wait for processing
3. Check recording details

**Expected**:
- **Confidence Score**: ≥ 0.7
- **Quality Score**: ≥ 0.6
- **AI Decision**: `approved` or `upload_approved`
- **Status**: Progresses to cloud upload queue

### 4.3 Test Low-Quality Recording

1. Upload audio with:
   - Background noise
   - Poor clarity
   - Muffled speech
2. Wait for processing
3. Check recording details

**Expected**:
- **Confidence Score**: < 0.7 OR **Quality Score**: < 0.6
- **AI Decision**: `rejected_low_quality`
- **Status**: `failed` or marked for manual review
- **Not queued for cloud upload**

### 4.4 Verify AI Decision Logging

```bash
# Check logs for AI decisions
docker compose logs queue | grep "AI Filter"

# Expected log entries:
# - "AI Filter: Evaluating recording ID: X"
# - "AI Filter: Decision = approved" OR "rejected_low_quality"
# - "AI Filter: Confidence = 0.XX, Quality = 0.XX"
```

### 4.5 Check Database Fields

```bash
docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
  SELECT 
    id, 
    file_name, 
    confidence_score, 
    quality_score, 
    ai_decision, 
    metadata 
  FROM recording_details 
  WHERE confidence_score IS NOT NULL 
  ORDER BY created_at DESC 
  LIMIT 5;
"
```

**Expected**: All AI-related fields populated correctly

✅ **Success Criteria**: AI evaluates quality, decisions logged, low-quality recordings rejected

---

## Test Suite 5: PII Detection & Privacy

### 4.1 Enable PII Detection

Verify in `.env`:
```bash
AI_ENABLE_PII_DETECTION=true
AI_ENABLE_PII_REDACTION=true
```

### 5.2 Test PII Detection

1. Upload recording with spoken PII:
   - Social Security Number: "My SSN is 123-45-6789"
   - Credit Card: "My card number is 4532-1234-5678-9010"
   - Email: "Contact me at john.doe@example.com"

2. Wait for STT and AI processing

3. Check recording details

**Expected**:
- **PII Detected**: `true`
- **PII Types**: Array like `["ssn", "credit_card", "email"]`
- **AI Decision**: May flag for redaction
- **Metadata**: Contains PII detection details

### 5.3 Verify PII Redaction

If redaction is enabled:

1. View transcript in UI
2. **Expected**: Sensitive data redacted:
   - "My SSN is [REDACTED]"
   - "My card number is [REDACTED]"
   - "Contact me at [REDACTED]"

### 5.4 Check PII Logging

```bash
# Check logs for PII detection
docker compose logs queue | grep "PII"

# Expected:
# - "PII Detection: Found X types of PII"
# - "PII Redaction: Applied to transcript"
```

### 5.5 Database Verification

```bash
docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
  SELECT id, file_name, pii_detected, pii_types, pii_redacted 
  FROM recording_details 
  WHERE pii_detected = 1;
"
```

✅ **Success Criteria**: PII detected, flagged, redacted where applicable

---

## Test Suite 6: Selective Upload Logic

### 6.1 Verify Upload Decisions

Test different scenarios:

| Scenario | Confidence | Quality | PII | Expected Decision |
|----------|-----------|---------|-----|-------------------|
| Scenario A | 0.9 | 0.8 | No | `upload_approved` |
| Scenario B | 0.5 | 0.7 | No | `rejected_low_quality` |
| Scenario C | 0.8 | 0.7 | Yes | `flagged_pii` (may upload with redaction) |
| Scenario D | 0.4 | 0.3 | No | `rejected_low_quality` |

### 6.2 Check Upload Queue

```bash
# View pending cloud uploads
docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
  SELECT id, file_name, ai_decision, status 
  FROM recording_details 
  WHERE ai_decision = 'upload_approved' 
  AND status IN ('pending_upload', 'uploading');
"
```

**Expected**: Only approved recordings in upload queue

### 6.3 Verify Rejected Recordings Don't Upload

1. Upload low-quality recording
2. Wait for AI decision: `rejected_low_quality`
3. Check upload queue

**Expected**: Recording NOT in upload queue, status NOT `pending_upload`

✅ **Success Criteria**: Only approved recordings queued for upload

---

## Test Suite 7: Encryption & Compression

### 7.1 Configure Encryption Key

Verify in `.env`:
```bash
ENCRYPTION_KEY="your-32-character-encryption-key"
```

### 7.2 Test File Encryption

1. Upload a recording that passes AI filtering
2. Wait for processing and encryption
3. Check storage directory:

```bash
# View encrypted files
docker compose exec app ls -lh storage/app/encrypted/

# Expected: .enc files present
```

### 7.3 Verify Encryption Metadata

```bash
docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
  SELECT id, file_name, encrypted, encryption_method, compressed 
  FROM recording_details 
  WHERE encrypted = 1;
"
```

**Expected fields**:
- `encrypted`: 1 (true)
- `encryption_method`: `aes-256-cbc`
- `compressed`: 1 (if compression applied)

### 7.4 Check File Sizes

```bash
# Compare original vs encrypted file sizes
docker compose exec app du -h storage/app/recordings/original.wav
docker compose exec app du -h storage/app/encrypted/original.wav.enc

# Expected: Encrypted file slightly larger (due to padding)
# If compressed: Encrypted file should be smaller
```

### 7.5 Verify Encryption in Logs

```bash
docker compose logs queue | grep -i "encrypt"

# Expected:
# - "Encrypting recording ID: X"
# - "Encryption successful"
# - "File saved to: storage/app/encrypted/..."
```

✅ **Success Criteria**: Files encrypted, metadata saved, ready for secure transfer

---

## Test Suite 8: Telemetry Monitoring

### 8.1 Access Telemetry Endpoint

```bash
# Call telemetry endpoint
curl http://localhost:8080/status/local
```

**Expected JSON response**:
```json
{
  "status": "operational",
  "timestamp": "2025-01-29T14:30:00Z",
  "queue_size": 5,
  "recordings": {
    "total": 150,
    "pending": 5,
    "processing": 2,
    "completed": 130,
    "failed": 3
  },
  "uploads": {
    "pending": 10,
    "in_progress": 2,
    "completed": 85,
    "failed": 1
  },
  "storage": {
    "used_mb": 2048,
    "available_mb": 8000
  },
  "ai_stats": {
    "approved": 120,
    "rejected_low_quality": 20,
    "flagged_pii": 10
  }
}
```

### 8.2 Verify Telemetry Logging

```bash
# Check telemetry logs table
docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
  SELECT * FROM telemetry_logs ORDER BY created_at DESC LIMIT 5;
"
```

**Expected**: Regular telemetry entries with metrics

### 8.3 Test Telemetry Service

```bash
# Run artisan command to log telemetry
docker compose exec app php artisan tinker

# In Tinker:
app(\App\Services\TelemetryService::class)->logMetrics([
    'test_metric' => 'manual_test',
    'value' => 123
]);

# Exit: exit
```

**Expected**: New entry in `telemetry_logs` table

✅ **Success Criteria**: Telemetry endpoint accessible, metrics accurate, logs captured

---

## Test Suite 9: Queue Processing

### 9.1 Monitor Queue Activity

```bash
# Watch queue in real-time
docker compose logs -f queue
```

### 9.2 Check Queue Statistics

```bash
# View queue jobs
docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
  SELECT * FROM jobs ORDER BY created_at DESC LIMIT 10;
"

# View failed jobs
docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
  SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 5;
"
```

### 9.3 Test Queue Retry

If there are failed jobs:

```bash
# Retry failed jobs
docker compose exec app php artisan queue:retry all
```

### 9.4 Simulate Queue Load

1. Upload 5-10 recordings simultaneously
2. Watch queue logs
3. **Expected**: Jobs processed sequentially, no crashes

✅ **Success Criteria**: Queue processes jobs reliably, failures logged, retries work

---

## Test Suite 10: End-to-End Workflow

### 10.1 Complete Workflow Test

**Scenario**: Upload recording with ticket, process through AI pipeline, encrypt, prepare for cloud transfer

**Steps**:

1. **Import Ticket Data**:
   - Import CSV with ticket: `TICKET999`

2. **Upload Recording**:
   - Filename: `TICKET999_customer_issue.wav`
   - Select "Yes, requires ticket"
   - Upload file

3. **Monitor Processing**:
   ```bash
   docker compose logs -f queue
   ```

4. **Verify Each Stage**:
   - ✅ STT processing completed
   - ✅ Transcript generated
   - ✅ Transcript cleaned (filler words removed)
   - ✅ Ticket linked (TICKET999)
   - ✅ AI quality evaluation passed
   - ✅ PII detection ran
   - ✅ Confidence & quality scores calculated
   - ✅ AI decision: `upload_approved`
   - ✅ File encrypted
   - ✅ Queued for cloud upload
   - ✅ Telemetry logged

5. **Check Final State**:
   ```bash
   docker compose exec mysql mysql -u laravel_user -plaravel_password onprem_kontakami -e "
     SELECT 
       id, file_name, status, ticket_id, customer_name, 
       confidence_score, quality_score, ai_decision, 
       pii_detected, encrypted, 
       created_at, updated_at 
     FROM recording_details 
     WHERE file_name LIKE '%TICKET999%';
   "
   ```

**Expected Final State**:
- Status: `pending_upload` or `uploading`
- Ticket ID: TICKET999
- Customer Name: (from CSV)
- Confidence Score: ≥ 0.7
- Quality Score: ≥ 0.6
- AI Decision: `upload_approved`
- Encrypted: 1
- All metadata fields populated

### 10.2 Verify Cloud Transfer Preparation

```bash
# Check CloudTransferService would prepare correct data
docker compose exec app php artisan tinker

# In Tinker:
$service = app(\App\Services\CloudTransferService::class);
$recording = \App\Models\Data\RecordingDetail::where('file_name', 'LIKE', '%TICKET999%')->first();
$data = $service->prepareTransferData($recording);
dd($data);
```

**Expected data structure**:
```php
[
  'recording_id' => 'XXX',
  'file_path' => 'storage/app/encrypted/...',
  'ticket_data' => [...],
  'ai_metadata' => [
    'confidence_score' => 0.85,
    'quality_score' => 0.78,
    'ai_decision' => 'upload_approved',
    'pii_detected' => false,
  ],
  'encryption' => [
    'method' => 'aes-256-cbc',
    'encrypted' => true,
  ],
]
```

✅ **Success Criteria**: Complete workflow executes successfully, all stages verified, data ready for cloud transfer

---

## Performance Testing

### Concurrent Uploads

1. Upload 10 recordings simultaneously
2. Monitor system resources:
   ```bash
   docker stats
   ```
3. **Expected**: All recordings processed without crashes

### Large File Test

1. Upload 100MB+ audio file
2. **Expected**: Upload succeeds, processing completes (may take longer)

---

## Common Issues & Solutions

### Issue: "Queue not processing jobs"

**Solution**:
```bash
docker compose logs queue
docker compose restart queue
```

### Issue: "AI evaluation fails"

**Check**:
1. Gemini API key is correct
2. API quota not exceeded
3. Network connectivity

```bash
# Test Gemini API
curl -H "Content-Type: application/json" \
  -d '{"contents":[{"parts":[{"text":"Test"}]}]}' \
  "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key=YOUR_KEY"
```

### Issue: "Encryption fails"

**Check**:
1. `ENCRYPTION_KEY` is exactly 32 characters
2. Storage directory is writable

```bash
docker compose exec app ls -ld storage/app/encrypted
docker compose exec app chmod -R 775 storage/app/encrypted
```

---

## Test Result Documentation

Create a test results file:

```bash
cp /app/ahs8888-Onprem-Kontakami-AI-cd0e83a/MANUAL_TESTING_RESULTS_TEMPLATE.md test_results_$(date +%Y%m%d).md
```

Fill in results for each test suite:
- ✅ Passed
- ❌ Failed (with details)
- ⚠️ Partial (with notes)

---

## Next Steps After Testing

Once all tests pass:

1. Document any bugs found
2. Create GitHub issues for fixes
3. Proceed to **Cloud App Enhancement** (Phase 10B)
4. Test end-to-end integration (on-prem → cloud)

---

## Support

For testing issues:
1. Check `docker compose logs -f`
2. Review `DOCKER_SETUP_README.md`
3. Consult `PHASE_9_SETUP.md` for feature details
