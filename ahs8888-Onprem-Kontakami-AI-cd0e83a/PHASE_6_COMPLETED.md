# Phase 6 Completion: Cloud Transfer with Ticket Information

## Summary

Phase 6 (Cloud Transfer Logic) has been completed. The system now automatically sends ticket information to the cloud when recordings are transferred, and can retroactively update existing cloud recordings when ticket information is added.

## What Was Built

### 1. CloudTransferService (`app/Services/CloudTransferService.php`)

A new service to handle cloud transfer data preparation with ticket information:

**Key Methods:**
- `prepareRecordingData()` - Prepares single recording data with ticket info
- `prepareRecordingsBatch()` - Prepares multiple recordings for batch transfer
- `getRecordingsNeedingTicketSync()` - Finds recordings that need cloud updates
- `hasTicketInfo()` - Checks if a recording has ticket data

**Data Structure:** When ticket information exists, recordings are sent to cloud with:
```php
[
    'filename' => 'recording.wav',
    'size' => '2.5 MB',
    'token' => 1234,
    'transcribe' => 'transcription text...',
    'ticket_info' => [
        'ticket_id' => 'TKT-12345',
        'ticket_url' => 'https://...',
        'customer_name' => 'John Doe',
        'agent_name' => 'Agent Smith',
        'call_intent' => 'Support Request',
        'call_outcome' => 'Resolved',
        'display_name' => 'JohnDoe_TKT12345_20250129',
        'linked_at' => '2025-01-29T10:30:00Z'
    ]
]
```

### 2. UpdateCloudTicketInfo Job (`app/Jobs/UpdateCloudTicketInfo.php`)

A queue job for retroactively updating ticket information in the cloud:

**Features:**
- Dispatched automatically when ticket info is added to already-transferred recordings
- Can update specific recordings or all recordings in a folder
- Sends updates to cloud endpoint: `/api/external/v1/recording/update-ticket/{clouds_uuid}`
- Comprehensive logging for debugging
- Graceful error handling

**When It's Triggered:**
1. **Automatically:** When linking a recording that's already in the cloud
2. **Bulk CSV Import:** After importing tickets for transferred recordings
3. **Manually:** Via console command

### 3. Updated Existing Jobs & Commands

All cloud transfer components now include ticket information:

#### ProcessRecordingBatch Job
- ✅ Now uses `CloudTransferService` to prepare data
- ✅ Includes ticket information in initial transfer

#### RetryRecording Job
- ✅ Uses `CloudTransferService` for retry transfers
- ✅ Includes ticket information if available

#### RetryUploadClouds Job
- ✅ Uses `CloudTransferService` for batch uploads
- ✅ Includes ticket information

#### UploadClouds Command
- ✅ Uses `CloudTransferService` for command-line uploads
- ✅ Includes ticket information

### 4. Updated TicketLinkingService

Enhanced to support cloud updates:

**New Logic in `linkRecordingToTicket()`:**
- Checks if recording was already transferred to cloud (`transfer_cloud = 1`)
- If yes, automatically dispatches `UpdateCloudTicketInfo` job
- Logs all actions for audit trail

**Enhanced `bulkLinkFromCSV()`:**
- Tracks which recordings need cloud updates
- Handles bulk CSV imports efficiently
- Dispatches cloud updates automatically

### 5. Console Command: `ticket:sync-to-cloud`

New Artisan command for manual synchronization:

```bash
# Sync specific recording folder
php artisan ticket:sync-to-cloud {recording_id}

# Sync all recordings with ticket info
php artisan ticket:sync-to-cloud --all
```

**Use Cases:**
- Manual sync after system downtime
- Re-sync after cloud API issues
- Testing synchronization
- Bulk sync of historical data

## How It Works

### Scenario 1: New Recording Upload (With Ticket Info Already Set)

1. User uploads recording folder with "Requires Ticket" checked
2. Recording is processed (STT via Gemini)
3. **CloudTransferService** prepares data with ticket fields (if available)
4. Data sent to cloud includes `ticket_info` object
5. Recording marked as `transfer_cloud = 1`

### Scenario 2: Adding Ticket Info to Already-Transferred Recording

1. Recording already in cloud (`transfer_cloud = 1`)
2. Admin imports CSV with ticket information
3. **TicketLinkingService** links ticket data to recording
4. Detects recording was already transferred
5. **Automatically dispatches** `UpdateCloudTicketInfo` job
6. Job sends update to cloud endpoint
7. Cloud receives and updates ticket information

### Scenario 3: Bulk CSV Import

1. Admin uploads CSV with 100 recordings
2. 50 recordings already in cloud, 50 not yet transferred
3. **Bulk linking process:**
   - All 100 recordings get ticket info in database
   - 50 already-transferred recordings trigger cloud updates
   - 50 not-yet-transferred will include ticket info when transferred later

### Scenario 4: Manual Retroactive Sync

1. Admin discovers some recordings didn't sync
2. Runs: `php artisan ticket:sync-to-cloud --all`
3. System finds all recordings with:
   - `clouds_uuid` (in cloud)
   - `ticket_id` (has ticket info)
   - `transfer_cloud = 1` (marked as transferred)
4. Dispatches update jobs for each
5. Cloud receives all updates

## Cloud App Changes Required

⚠️ **Important:** The cloud application needs to be updated to handle the new data structure.

### New Endpoint Needed

The on-prem app sends ticket updates to:
```
POST /api/external/v1/recording/update-ticket/{clouds_uuid}
```

**Request Body:**
```json
{
  "files": [
    {
      "filename": "recording_001.wav",
      "size": "2.5 MB",
      "token": 1234,
      "transcribe": "transcription text...",
      "ticket_info": {
        "ticket_id": "TKT-12345",
        "ticket_url": "https://tickets.example.com/12345",
        "customer_name": "John Doe",
        "agent_name": "Agent Smith",
        "call_intent": "Support Request",
        "call_outcome": "Resolved",
        "display_name": "JohnDoe_TKT12345_20250129",
        "linked_at": "2025-01-29T10:30:00Z"
      }
    }
  ]
}
```

### Existing Endpoints

The existing endpoints `/api/external/v1/recording` and `/api/external/v1/recording/inject/{uuid}` should also be updated to accept and store `ticket_info` if present.

## Testing

### Test 1: New Recording with Ticket Info

1. Set up Laravel environment (composer install, migrations)
2. Upload a recording folder (mark "Requires Ticket")
3. Import CSV with ticket information
4. Check logs for cloud transfer
5. Verify cloud received `ticket_info` object

### Test 2: Retroactive Update

1. Upload and transfer recording without ticket info
2. Verify recording in cloud
3. Import CSV to add ticket information
4. Check logs for `UpdateCloudTicketInfo` job dispatch
5. Verify cloud received update

### Test 3: Bulk Import

1. Have mix of transferred/not-transferred recordings
2. Import CSV with all ticket information
3. Verify only transferred recordings trigger cloud updates
4. Check queue job logs
5. Verify cloud data is correct

### Test 4: Manual Sync Command

```bash
# Test single recording
php artisan ticket:sync-to-cloud abc-123-def

# Test all recordings
php artisan ticket:sync-to-cloud --all
```

## Configuration

### Queue Configuration

The `UpdateCloudTicketInfo` job uses Laravel queues. Ensure queue worker is running:

```bash
php artisan queue:work
```

For production, use Supervisor or similar process manager.

### Cloud URL Configuration

Set in `config/services.php`:
```php
'clouds_url' => env('CLOUDS_URL', 'https://cloud.example.com'),
```

### Logging

All cloud transfers and updates are logged:
- `Log::info()` for successful operations
- `Log::warning()` for missing data
- `Log::error()` for failures

Check logs at `storage/logs/laravel.log`

## Database Changes

No new migrations needed. Phase 6 uses fields added in Phase 1:
- `ticket_id`
- `ticket_url`
- `customer_name`
- `agent_name`
- `call_intent`
- `call_outcome`
- `display_name`
- `linked_at`
- `transfer_cloud` (existing field)

## File Structure

```
/app/ahs8888-Onprem-Kontakami-AI-cd0e83a/
├── app/
│   ├── Console/Commands/
│   │   ├── UploadClouds.php (updated)
│   │   └── SyncTicketInfoToCloud.php (new)
│   ├── Jobs/
│   │   ├── ProcessRecordingBatch.php (updated)
│   │   ├── RetryRecording.php (updated)
│   │   ├── RetryUploadClouds.php (updated)
│   │   └── UpdateCloudTicketInfo.php (new)
│   └── Services/
│       ├── CloudTransferService.php (new)
│       └── TicketLinkingService.php (updated)
```

## Benefits

✅ **Automatic Sync:** Ticket info automatically synced to cloud
✅ **Retroactive Updates:** Can update recordings already in cloud
✅ **Bulk Operations:** Efficient handling of many recordings
✅ **Audit Trail:** Comprehensive logging
✅ **Fault Tolerant:** Queue-based with retry capability
✅ **Manual Override:** Console command for manual sync
✅ **Flexible:** Works with existing and new recordings

## Known Limitations

1. **Cloud API Dependency:** Requires cloud app to implement new endpoint
2. **Queue Required:** Need queue worker running for automatic updates
3. **Network Dependency:** Updates fail if cloud API unavailable (will retry via queue)

## Next Steps (Phase 7 & 8)

- **Phase 7:** UI polish and navigation improvements
- **Phase 8:** Comprehensive testing and validation

## Notes

- All cloud transfers now include ticket information when available
- Retroactive updates happen automatically via queue jobs
- Manual sync available via console command
- Cloud app must be updated to receive and store ticket information
- Comprehensive logging ensures traceability
